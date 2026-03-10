<?php

use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

test('guests are redirected to login when visiting clients and prospects', function () {
    $response = $this->get(route('sales.clients-and-prospects'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the clients and prospects index', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('sales.clients-and-prospects'));

    $response->assertOk();
});

test('authenticated users can visit the create customer page', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('sales.clients-and-prospects.create'));

    $response->assertOk();
});

test('authenticated users can create a customer', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1 555-1234',
    ];

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('sales.clients-and-prospects.store'), $data);

    $response->assertRedirect(route('sales.clients-and-prospects'));
    $response->assertSessionHas('success', 'Customer created successfully.');

    $this->assertDatabaseHas('clients', [
        'organization_id' => $org->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1 555-1234',
    ]);
});

test('index returns customers from database', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    Client::factory()->create([
        'organization_id' => $org->id,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'phone' => '+1 555-5678',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('sales.clients-and-prospects'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sale/ClientsAndProspects')
        ->has('customers', 1)
        ->where('customers.0.name', 'Jane Smith')
    );
});
