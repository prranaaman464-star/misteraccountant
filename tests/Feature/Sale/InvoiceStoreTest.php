<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;

test('guests are redirected when trying to store an invoice', function () {
    $response = $this->post(route('sales.invoices.store'), []);

    $response->assertRedirect();
});

test('authenticated users can create an invoice', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);
    $client = Client::factory()->create(['organization_id' => $org->id]);

    $data = [
        'invoice_number' => 'INV-0000123',
        'reference_number' => 'REF-001',
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'draft',
        'currency' => 'USD',
        'enable_tax' => true,
        'round_off_total' => true,
        'discount_percent' => 0,
        'subtotal_amount' => 100,
        'cgst_amount' => 9,
        'sgst_amount' => 9,
        'discount_amount' => 0,
        'total_amount' => 118,
        'is_recurring' => false,
        'line_items' => [
            [
                'product_name' => 'Test Product',
                'quantity' => 1,
                'unit' => 'Pcs',
                'rate' => 100,
                'discount' => 0,
                'tax_percent' => 18,
                'amount' => 118,
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('sales.invoices.store'), $data);

    $response->assertRedirect(route('sales.invoices'));
    $response->assertSessionHas('success', 'Invoice created successfully.');

    $this->assertDatabaseHas('sales_invoices', [
        'organization_id' => $org->id,
        'client_id' => null,
        'invoice_number' => 'INV-0000123',
        'status' => 'draft',
        'currency' => 'USD',
        'total_amount' => 118,
    ]);

    $invoice = Invoice::withoutGlobalScopes()->where('invoice_number', 'INV-0000123')->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->lineItems)->toHaveCount(1);
    expect($invoice->lineItems->first()->product_name)->toBe('Test Product');
});

test('authenticated users can create an invoice with a customer', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);
    $client = Client::factory()->create(['organization_id' => $org->id]);

    $data = [
        'invoice_number' => 'INV-0000456',
        'invoice_date' => now()->format('Y-m-d'),
        'currency' => 'USD',
        'customer_id' => (string) $client->id,
        'line_items' => [
            [
                'product_name' => 'Consulting Service',
                'quantity' => 2,
                'unit' => 'Hours',
                'rate' => 150,
                'discount' => 10,
                'tax_percent' => 18,
                'amount' => 318.6,
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('sales.invoices.store'), $data);

    $response->assertRedirect(route('sales.invoices'));

    $this->assertDatabaseHas('sales_invoices', [
        'organization_id' => $org->id,
        'client_id' => $client->id,
        'invoice_number' => 'INV-0000456',
    ]);
});

test('authenticated users can view invoice templates', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('sales.invoices.templates'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sale/InvoicesTemplates')
    );
});

test('authenticated users can view an invoice', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);
    $client = Client::factory()->create(['organization_id' => $org->id]);

    $invoice = Invoice::withoutGlobalScopes()->create([
        'organization_id' => $org->id,
        'client_id' => $client->id,
        'invoice_number' => 'INV-0000789',
        'reference_number' => 'REF-789',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
        'status' => 'draft',
        'currency' => 'USD',
        'enable_tax' => true,
        'round_off_total' => true,
        'discount_percent' => 0,
        'subtotal_amount' => 100,
        'cgst_amount' => 9,
        'sgst_amount' => 9,
        'discount_amount' => 0,
        'total_amount' => 118,
        'is_recurring' => false,
    ]);

    $invoice->lineItems()->create([
        'product_name' => 'Test Product',
        'quantity' => 1,
        'unit' => 'Pcs',
        'rate' => 100,
        'discount_percent' => 0,
        'tax_percent' => 18,
        'amount' => 118,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('sales.invoices.show', $invoice));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sale/InvoicesShow')
        ->where('invoice.id', (string) $invoice->id)
        ->where('invoice.client.name', $client->name)
        ->where('invoice.line_items.0.product_name', 'Test Product')
    );
});
