<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Item;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoicesController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $stats = [
            'total' => ['value' => 25000, 'change' => 5.62, 'trend' => 'up'],
            'paid' => ['value' => 18500, 'change' => 11.4, 'trend' => 'up'],
            'pending' => ['value' => 6500, 'change' => 8.52, 'trend' => 'up'],
            'overdue' => ['value' => 2000, 'change' => -7.45, 'trend' => 'down'],
        ];

        $invoices = [
            [
                'id' => 'INV00025',
                'customer' => ['name' => 'Emily Clark', 'avatar' => null],
                'created_on' => '2025-02-22',
                'amount' => 10000,
                'paid' => 5000,
                'status' => 'paid',
                'payment_mode' => 'Cash',
                'due_date' => '2025-03-04',
            ],
            [
                'id' => 'INV00024',
                'customer' => ['name' => 'John Carter', 'avatar' => null],
                'created_on' => '2025-02-07',
                'amount' => 25750,
                'paid' => 10750,
                'status' => 'refunded',
                'payment_mode' => 'Check',
                'due_date' => '2025-03-20',
            ],
        ];

        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;
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
}
