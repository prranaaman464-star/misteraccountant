<?php

use App\Models\Organization;
use App\Models\User;

test('guests are redirected to the login page when visiting inventory items', function () {
    $response = $this->get(route('inventory.items'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the inventory items index', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('inventory.items'));
    $response->assertOk();
});

test('authenticated users can create an item', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $data = [
        'name' => 'Test Item',
        'item_code' => 'SKU-001',
        'item_type' => 'goods',
        'status' => 'active',
    ];

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('inventory.items.store'), $data);

    $response->assertRedirect(route('inventory.items'));
    $this->assertDatabaseHas('items', [
        'name' => 'Test Item',
        'item_code' => 'SKU-001',
        'item_type' => 'goods',
        'status' => 'active',
    ]);
    $item = \App\Models\Item::query()->where('item_code', 'SKU-001')->first();
    expect($item)->not->toBeNull()
        ->and($item->taxDetail)->not->toBeNull()
        ->and($item->pricing)->not->toBeNull()
        ->and($item->inventory)->not->toBeNull()
        ->and($item->compliance)->not->toBeNull();
});

test('guests cannot create an item', function () {
    $response = $this->post(route('inventory.items.store'), [
        'name' => 'Test Item',
        'item_type' => 'goods',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseCount('items', 0);
});
