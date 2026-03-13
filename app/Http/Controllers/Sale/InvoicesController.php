<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InvoicesController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;

        $invoices = [];
        $stats = [
            'total' => ['value' => 0, 'change' => 0, 'trend' => 'up'],
            'paid' => ['value' => 0, 'change' => 0, 'trend' => 'up'],
            'pending' => ['value' => 0, 'change' => 0, 'trend' => 'up'],
            'overdue' => ['value' => 0, 'change' => 0, 'trend' => 'down'],
        ];

        if ($organizationId) {
            $query = Invoice::withoutGlobalScopes()
                ->with('client')
                ->where('organization_id', $organizationId)
                ->latest('invoice_date');

            $allInvoices = $query->get();

            $stats['total']['value'] = (float) $allInvoices->sum('total_amount');
            $stats['paid']['value'] = (float) $allInvoices->where('status', 'paid')->sum('total_amount');
            $stats['pending']['value'] = (float) $allInvoices->whereIn('status', ['draft', 'sent', 'unpaid', 'partially_paid'])->sum('total_amount');
            $stats['overdue']['value'] = (float) $allInvoices->filter(function ($inv) {
                if ($inv->status === 'paid') {
                    return false;
                }

                return $inv->due_date && $inv->due_date->isPast();
            })->sum('total_amount');

            $invoices = $allInvoices->map(fn (Invoice $inv) => [
                'id' => (string) $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer' => [
                    'name' => $inv->client?->name ?? '—',
                    'avatar' => $inv->client?->avatar ? Storage::url($inv->client->avatar) : null,
                ],
                'created_on' => $inv->invoice_date->format('Y-m-d'),
                'amount' => (float) $inv->total_amount,
                'paid' => 0,
                'status' => $inv->status ?: 'draft',
                'payment_mode' => '—',
                'due_date' => $inv->due_date?->format('Y-m-d') ?? '—',
                'currency' => $inv->currency ?? 'USD',
            ])->values()->all();
        }

        $teamMembers = [];
        if ($organizationId) {
            $org = Organization::find($organizationId);
            if ($org && $request->user()?->belongsToOrganization($org)) {
                $teamMembers = $org->users()
                    ->wherePivot('is_active', true)
                    ->limit(5)
                    ->get(['users.id', 'users.name'])
                    ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                    ->values()
                    ->all();
            }
        }

        return Inertia::render('sale/Invoices', [
            'stats' => $stats,
            'invoices' => $invoices,
            'teamMembers' => $teamMembers,
        ]);
    }

    public function create(Request $request): \Inertia\Response
    {
        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;

        $clients = [];
        $products = [];
        $teamMembers = [];
        if ($organizationId) {
            $clients = Client::query()
                ->where('organization_id', $organizationId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($c) => ['id' => (string) $c->id, 'name' => $c->name])
                ->values()
                ->all();

            $products = Item::query()
                ->with('pricing')
                ->whereIn('item_type', ['goods', 'service', 'finished_goods'])
                ->orderBy('name')
                ->get()
                ->map(fn ($i) => [
                    'id' => (string) $i->id,
                    'name' => $i->name,
                    'item_type' => $i->item_type ?? 'goods',
                    'rate' => (float) ($i->pricing?->sale_price ?? 0),
                    'unit' => 'Pcs',
                    'tax_rate' => 0,
                ])
                ->values()
                ->all();

            $org = Organization::find($organizationId);
            if ($org && $request->user()?->belongsToOrganization($org)) {
                $teamMembers = $org->users()
                    ->wherePivot('is_active', true)
                    ->get(['users.id', 'users.name'])
                    ->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])
                    ->values()
                    ->all();
            }
        }

        $currencies = [
            'USD' => 'USD - US Dollar',
            'INR' => 'INR - Indian Rupee',
            'EUR' => 'EUR - Euro',
            'GBP' => 'GBP - British Pound',
        ];

        $statuses = [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
        ];

        $signatures = [
            ['id' => '1', 'name' => 'Adrian'],
            ['id' => '2', 'name' => 'Emily Clark'],
            ['id' => '3', 'name' => 'John Carter'],
            ['id' => '4', 'name' => 'Michael Johnson'],
            ['id' => '5', 'name' => 'Olivia Harris'],
        ];

        $invoiceNumber = 'INV-'.str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT);
        $referenceNumber = str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT);

        return Inertia::render('sale/InvoicesCreate', [
            'clients' => $clients,
            'products' => $products,
            'teamMembers' => $teamMembers,
            'currencies' => $currencies,
            'statuses' => $statuses,
            'signatures' => $signatures,
            'invoiceNumber' => $invoiceNumber,
            'referenceNumber' => $referenceNumber,
        ]);
    }

    public function templates(Request $request): \Inertia\Response
    {
        return Inertia::render('sale/InvoicesTemplates');
    }

    public function recurring(Request $request): \Inertia\Response
    {
        return Inertia::render('sale/InvoicesRecurring');
    }

    public function details(Request $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;

        if (! $organizationId) {
            return redirect()->route('sales.invoices');
        }

        $invoice = Invoice::withoutGlobalScopes()
            ->with(['client', 'lineItems', 'billedByUser', 'organization'])
            ->where('organization_id', $organizationId)
            ->latest('invoice_date')
            ->first();

        if (! $invoice) {
            return Inertia::render('sale/InvoicesDetails', [
                'invoice' => null,
                'currencies' => [
                    'USD' => 'USD - US Dollar',
                    'INR' => 'INR - Indian Rupee',
                    'EUR' => 'EUR - Euro',
                    'GBP' => 'GBP - British Pound',
                ],
                'statuses' => [
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                    'unpaid' => 'Unpaid',
                    'partially_paid' => 'Partially Paid',
                ],
            ]);
        }

        $currencies = [
            'USD' => 'USD - US Dollar',
            'INR' => 'INR - Indian Rupee',
            'EUR' => 'EUR - Euro',
            'GBP' => 'GBP - British Pound',
        ];

        $statuses = [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
        ];

        return Inertia::render('sale/InvoicesDetails', [
            'invoice' => [
                'id' => (string) $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'reference_number' => $invoice->reference_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'status' => $invoice->status,
                'currency' => $invoice->currency,
                'enable_tax' => $invoice->enable_tax,
                'round_off_total' => $invoice->round_off_total,
                'discount_percent' => (float) $invoice->discount_percent,
                'subtotal_amount' => (float) $invoice->subtotal_amount,
                'cgst_amount' => (float) $invoice->cgst_amount,
                'sgst_amount' => (float) $invoice->sgst_amount,
                'discount_amount' => (float) $invoice->discount_amount,
                'total_amount' => (float) $invoice->total_amount,
                'additional_notes' => $invoice->additional_notes,
                'terms_and_conditions' => $invoice->terms_and_conditions,
                'signature_name' => $invoice->signature_name,
                'is_recurring' => $invoice->is_recurring,
                'recurring_frequency' => $invoice->recurring_frequency,
                'recurring_interval' => $invoice->recurring_interval,
                'client' => $invoice->client ? [
                    'id' => (string) $invoice->client->id,
                    'name' => $invoice->client->name,
                    'email' => $invoice->client->email,
                    'phone' => $invoice->client->phone,
                    'billing_address' => implode(', ', array_filter([
                        $invoice->client->billing_address_line_1,
                        $invoice->client->billing_city,
                        $invoice->client->billing_state,
                        $invoice->client->billing_pincode,
                        $invoice->client->billing_country,
                    ])) ?: null,
                ] : null,
                'billed_by' => $invoice->billedByUser?->name,
                'line_items' => $invoice->lineItems->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit,
                    'rate' => (float) $item->rate,
                    'discount_percent' => (float) $item->discount_percent,
                    'tax_percent' => (float) $item->tax_percent,
                    'amount' => (float) $item->amount,
                ])->values()->all(),
            ],
            'currencies' => $currencies,
            'statuses' => $statuses,
            'organization' => $invoice->organization ? [
                'name' => $invoice->organization->name,
                'address' => $invoice->organization->address,
                'phone' => $invoice->organization->phone,
                'email' => $invoice->organization->email,
                'gst_number' => null,
            ] : null,
        ]);
    }

    public function show(Request $request, Invoice $invoice): \Inertia\Response
    {
        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;

        if (! $organizationId || (int) $invoice->organization_id !== (int) $organizationId) {
            abort(404);
        }

        $invoice->load(['client', 'lineItems', 'billedByUser']);

        $currencies = [
            'USD' => 'USD - US Dollar',
            'INR' => 'INR - Indian Rupee',
            'EUR' => 'EUR - Euro',
            'GBP' => 'GBP - British Pound',
        ];

        $statuses = [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
        ];

        return Inertia::render('sale/InvoicesShow', [
            'invoice' => [
                'id' => (string) $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'reference_number' => $invoice->reference_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'status' => $invoice->status,
                'currency' => $invoice->currency,
                'enable_tax' => $invoice->enable_tax,
                'round_off_total' => $invoice->round_off_total,
                'discount_percent' => (float) $invoice->discount_percent,
                'subtotal_amount' => (float) $invoice->subtotal_amount,
                'cgst_amount' => (float) $invoice->cgst_amount,
                'sgst_amount' => (float) $invoice->sgst_amount,
                'discount_amount' => (float) $invoice->discount_amount,
                'total_amount' => (float) $invoice->total_amount,
                'additional_notes' => $invoice->additional_notes,
                'terms_and_conditions' => $invoice->terms_and_conditions,
                'signature_name' => $invoice->signature_name,
                'is_recurring' => $invoice->is_recurring,
                'recurring_frequency' => $invoice->recurring_frequency,
                'recurring_interval' => $invoice->recurring_interval,
                'client' => $invoice->client ? [
                    'id' => (string) $invoice->client->id,
                    'name' => $invoice->client->name,
                    'email' => $invoice->client->email,
                    'phone' => $invoice->client->phone,
                    'billing_address' => implode(', ', array_filter([
                        $invoice->client->billing_address_line_1,
                        $invoice->client->billing_city,
                        $invoice->client->billing_state,
                        $invoice->client->billing_pincode,
                        $invoice->client->billing_country,
                    ])) ?: null,
                ] : null,
                'billed_by' => $invoice->billedByUser?->name,
                'line_items' => $invoice->lineItems->map(fn ($item) => [
                    'id' => (string) $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit,
                    'rate' => (float) $item->rate,
                    'discount_percent' => (float) $item->discount_percent,
                    'tax_percent' => (float) $item->tax_percent,
                    'amount' => (float) $item->amount,
                ])->values()->all(),
            ],
            'currencies' => $currencies,
            'statuses' => $statuses,
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;

        $validated = $request->validated();

        $invoice = Invoice::withoutGlobalScopes()->create([
            'organization_id' => $organizationId,
            'client_id' => ! empty($validated['customer_id']) ? (int) $validated['customer_id'] : null,
            'invoice_number' => $validated['invoice_number'],
            'reference_number' => $validated['reference_number'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'currency' => $validated['currency'],
            'enable_tax' => (bool) ($validated['enable_tax'] ?? true),
            'billed_by' => ! empty($validated['billed_by']) ? (int) $validated['billed_by'] : null,
            'round_off_total' => (bool) ($validated['round_off_total'] ?? true),
            'discount_percent' => (float) ($validated['discount_percent'] ?? 0),
            'subtotal_amount' => (float) ($validated['subtotal_amount'] ?? 0),
            'cgst_amount' => (float) ($validated['cgst_amount'] ?? 0),
            'sgst_amount' => (float) ($validated['sgst_amount'] ?? 0),
            'discount_amount' => (float) ($validated['discount_amount'] ?? 0),
            'total_amount' => (float) ($validated['total_amount'] ?? 0),
            'additional_notes' => $validated['additional_notes'] ?? null,
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'account_id' => $validated['account_id'] ?? null,
            'selected_signature_id' => $validated['selected_signature_id'] ?? null,
            'signature_name' => $validated['signature_name'] ?? null,
            'is_recurring' => (bool) ($validated['is_recurring'] ?? false),
            'recurring_frequency' => $validated['recurring_frequency'] ?? null,
            'recurring_interval' => $validated['recurring_interval'] ?? null,
        ]);

        foreach ($validated['line_items'] as $lineItem) {
            $invoice->lineItems()->create([
                'item_id' => ! empty($lineItem['item_id']) ? (int) $lineItem['item_id'] : null,
                'product_name' => $lineItem['product_name'],
                'quantity' => (float) $lineItem['quantity'],
                'unit' => $lineItem['unit'],
                'rate' => (float) $lineItem['rate'],
                'discount_percent' => (float) ($lineItem['discount'] ?? 0),
                'tax_percent' => (float) ($lineItem['tax_percent'] ?? 0),
                'amount' => (float) $lineItem['amount'],
            ]);
        }

        return redirect()->route('sales.invoices')
            ->with('success', 'Invoice created successfully.');
    }
}
