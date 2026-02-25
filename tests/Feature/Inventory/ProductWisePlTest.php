<?php

use App\Models\Organization;
use App\Models\User;

test('guests are redirected to the login page when visiting product-wise P&L', function () {
    $response = $this->get(route('inventory.product-wise-pl'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the product-wise P&L page', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('inventory.product-wise-pl'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('inventory/ProductWise')
        ->has('items')
        ->has('items.data')
        ->has('items.current_page')
        ->has('filters')
    );
});

test('authenticated users can download product-wise P&L CSV', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('inventory.product-wise-pl.csv'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertHeader('content-disposition', 'attachment; filename="product-wise-profitability.csv"');
});
