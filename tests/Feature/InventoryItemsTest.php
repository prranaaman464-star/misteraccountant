<?php

use App\Models\Item;
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

test('authenticated users can edit an item', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $item = Item::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->put(route('inventory.items.update', $item), [
            'name' => 'Updated Item Name',
            'item_type' => 'goods',
            'status' => 'active',
        ]);

    $response->assertRedirect(route('inventory.items.show', $item));
    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'name' => 'Updated Item Name',
    ]);
});

test('authenticated users can stock in an item', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $item = Item::factory()->create();
    $item->inventory?->update(['stock_quantity' => 10]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('inventory.items.stock-in', $item), [
            'quantity' => 5,
            'unit' => 'Nos',
            'reference' => 'PO-001',
        ]);

    $response->assertRedirect();
    $item->refresh();
    expect((float) $item->inventory->stock_quantity)->toBe(15.0);
    $item->load('stockMovements');
    expect($item->stockMovements)->toHaveCount(1);
    expect($item->stockMovements->first()->type)->toBe('in');
    expect((float) $item->stockMovements->first()->quantity)->toBe(5.0);
    expect($item->stockMovements->first()->unit)->toBe('Nos');
});

test('authenticated users can stock out an item', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $item = Item::factory()->create();
    $item->inventory?->update(['stock_quantity' => 10]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('inventory.items.stock-out', $item), [
            'quantity' => 3,
            'unit' => 'Box',
            'reference' => 'SO-001',
        ]);

    $response->assertRedirect();
    $item->refresh();
    expect((float) $item->inventory->stock_quantity)->toBe(7.0);
    $item->load('stockMovements');
    expect($item->stockMovements)->toHaveCount(1);
    expect($item->stockMovements->first()->type)->toBe('out');
    expect($item->stockMovements->first()->unit)->toBe('Box');
});

test('stock out validation fails when quantity exceeds current stock', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $item = Item::factory()->create();
    $item->inventory?->update(['stock_quantity' => 5]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('inventory.items.stock-out', $item), [
            'quantity' => 10,
            'unit' => 'Nos',
            'reference' => '',
        ]);

    $response->assertSessionHasErrors('quantity');
    $item->refresh();
    expect((float) $item->inventory->stock_quantity)->toBe(5.0);
});

test('authenticated users can view an item with full details', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $item = Item::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('inventory.items.show', $item));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('inventory/ItemsShow')
        ->has('item')
        ->where('item.id', $item->id)
        ->where('item.name', $item->name)
    );
});
