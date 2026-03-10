<?php

namespace App\Http\Controllers\Sale;

use App\Helpers\CountryFlag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\StoreCustomerRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ClientsAndProspectsController extends Controller
{
    public function index(Request $request): Response
    {
        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;

        $query = Client::query()
            ->where('organization_id', $organizationId)
            ->latest();

        $customers = $query->get()->map(fn (Client $client) => [
            'id' => (string) $client->id,
            'name' => $client->name,
            'avatar' => $client->avatar ? Storage::url($client->avatar) : null,
            'phone' => $client->phone ?? '',
            'country' => $client->billing_country ?? $client->shipping_country ?? '-',
            'country_flag' => CountryFlag::get($client->billing_country ?? $client->shipping_country ?? ''),
            'balance' => (float) ($client->balance ?? 0),
            'total_invoice' => (int) ($client->total_invoice ?? 0),
            'created_on' => $client->created_at->format('Y-m-d'),
            'status' => $client->status === 'archived' ? 'inactive' : $client->status,
        ])->values()->all();

        return Inertia::render('sale/ClientsAndProspects', [
            'customers' => $customers,
        ]);
    }

    public function create(Request $request): Response
    {
        $currencies = [
            'INR' => 'INR - Indian Rupee',
            'USD' => 'USD - US Dollar',
            'EUR' => 'EUR - Euro',
            'GBP' => 'GBP - British Pound',
        ];

        $countries = [
            'India', 'USA', 'UK', 'Canada', 'Australia', 'Germany', 'France',
            'Japan', 'China', 'Singapore', 'UAE', 'Argentina', 'Brazil', 'Mexico',
        ];

        $states = [
            'Maharashtra', 'Karnataka', 'Tamil Nadu', 'Gujarat', 'Delhi',
            'California', 'Texas', 'Florida', 'New York', 'England', 'Scotland',
        ];

        $cities = [
            'Mumbai', 'Pune', 'Bangalore', 'Chennai', 'Delhi',
            'Los Angeles', 'San Francisco', 'London', 'Manchester',
        ];

        return Inertia::render('sale/ClientsAndProspectsCreate', [
            'currencies' => $currencies,
            'countries' => $countries,
            'states' => $states,
            'cities' => $cities,
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $organizationId = session('current_organization_id')
            ?? $request->user()?->organizations()->first()?->id;

        $validated = $request->validated();

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('customers', 'public');
        }

        Client::query()->create([
            'organization_id' => $organizationId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'notes' => $validated['notes'] ?? null,
            'avatar' => $avatarPath,
            'currency' => $validated['currency'] ?? null,
            'website' => $validated['website'] ?? null,
            'billing_name' => $validated['billing_name'] ?? null,
            'billing_address_line_1' => $validated['billing_address_line_1'] ?? null,
            'billing_address_line_2' => $validated['billing_address_line_2'] ?? null,
            'billing_country' => $validated['billing_country'] ?? null,
            'billing_state' => $validated['billing_state'] ?? null,
            'billing_city' => $validated['billing_city'] ?? null,
            'billing_pincode' => $validated['billing_pincode'] ?? null,
            'shipping_name' => $validated['shipping_name'] ?? null,
            'shipping_address_line_1' => $validated['shipping_address_line_1'] ?? null,
            'shipping_address_line_2' => $validated['shipping_address_line_2'] ?? null,
            'shipping_country' => $validated['shipping_country'] ?? null,
            'shipping_state' => $validated['shipping_state'] ?? null,
            'shipping_city' => $validated['shipping_city'] ?? null,
            'shipping_pincode' => $validated['shipping_pincode'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'account_holder' => $validated['account_holder'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'ifsc_code' => $validated['ifsc'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('sales.clients-and-prospects')
            ->with('success', 'Customer created successfully.');
    }
}
