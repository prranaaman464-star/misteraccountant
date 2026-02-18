<?php

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    $this->plan = Plan::factory()->create(['billing_cycle' => 'monthly']);
});

test('owner can renew plan', function () {
    $user = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $user->id]);
    $org->users()->attach($user->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);

    Subscription::create([
        'organization_id' => $org->id,
        'plan_id' => $this->plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addDays(5),
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('plan.renew'));

    $response->assertRedirect(route('plan.show'));
    $response->assertSessionHas('success');

    $sub = Subscription::where('organization_id', $org->id)->first();
    expect($sub->ends_at->gt(now()->addDays(5)))->toBeTrue();
});

test('admin can renew plan', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $owner->id]);
    $org->users()->attach($owner->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);
    $org->users()->attach($admin->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);

    Subscription::create([
        'organization_id' => $org->id,
        'plan_id' => $this->plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addDays(10),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('plan.renew'));

    $response->assertRedirect(route('plan.show'));
    $response->assertSessionHas('success');
});

test('staff cannot renew plan', function () {
    $owner = User::factory()->create();
    $staff = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $owner->id]);
    $org->users()->attach($owner->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);
    $org->users()->attach($staff->id, ['role' => 'staff', 'is_active' => true, 'joined_at' => now()]);

    Subscription::create([
        'organization_id' => $org->id,
        'plan_id' => $this->plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $response = $this->actingAs($staff)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('plan.renew'));

    $response->assertRedirect(route('plan.show'));
    $response->assertSessionHas('error');
});
