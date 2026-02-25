<?php

use App\Models\Item;
use App\Models\Organization;
use App\Models\User;

test('guests are redirected when accessing search', function () {
    $response = $this->get(route('search', ['q' => 'test']));
    $response->assertRedirect(route('login'));
});

test('authenticated users can search items', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $item = Item::factory()->create(['name' => 'Laptop Pro', 'item_code' => 'LAP-001']);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->getJson(route('search', ['q' => 'Laptop']));

    $response->assertOk();
    $data = $response->json();
    expect($data)->toHaveKey('results')
        ->and($data['results'])->toBeArray()
        ->and($data['results'])->toHaveCount(1)
        ->and($data['results'][0]['title'])->toBe('Laptop Pro')
        ->and($data['results'][0]['type'])->toBe('item')
        ->and($data['results'][0]['url'])->toContain("/inventory/items/{$item->id}");
});

test('search returns empty when query is too short', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->getJson(route('search', ['q' => 'a']));

    $response->assertOk();
    expect($response->json('results'))->toBeArray()->toBeEmpty();
});
