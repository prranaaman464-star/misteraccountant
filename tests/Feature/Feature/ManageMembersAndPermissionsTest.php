<?php

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    $this->plan = Plan::factory()->create(['member_limit' => 5]);
});

test('admin can add existing user as team member', function () {
    $admin = User::factory()->create();
    $existingUser = User::factory()->create(['email' => 'member@example.com']);
    $org = Organization::factory()->create(['owner_id' => $admin->id]);
    $org->users()->attach($admin->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);
    Subscription::create([
        'organization_id' => $org->id,
        'plan_id' => $this->plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('manage.members.store'), [
            'email' => 'member@example.com',
            'name' => '',
            'role' => 'staff',
        ]);

    $response->assertRedirect(route('manage.users'));
    $response->assertSessionHas('success');
    expect($org->users()->where('email', 'member@example.com')->exists())->toBeTrue();
});

test('admin can create permission', function () {
    $admin = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $admin->id]);
    $org->users()->attach($admin->id, ['role' => 'admin', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($admin)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('manage.permissions.store'), [
            'name' => 'Export reports',
            'key' => 'reports.export',
        ]);

    $response->assertRedirect(route('manage.permissions'));
    $response->assertSessionHas('success');
    expect($org->permissions()->where('key', 'reports.export')->exists())->toBeTrue();
});

test('staff cannot add team member', function () {
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
        ->post(route('manage.members.store'), [
            'email' => 'new@example.com',
            'name' => 'New User',
            'role' => 'staff',
        ]);

    $response->assertForbidden();
});

test('staff cannot create permission', function () {
    $owner = User::factory()->create();
    $staff = User::factory()->create();
    $org = Organization::factory()->create(['owner_id' => $owner->id]);
    $org->users()->attach($owner->id, ['role' => 'owner', 'is_active' => true, 'joined_at' => now()]);
    $org->users()->attach($staff->id, ['role' => 'staff', 'is_active' => true, 'joined_at' => now()]);

    $response = $this->actingAs($staff)
        ->withSession(['current_organization_id' => $org->id])
        ->post(route('manage.permissions.store'), [
            'name' => 'Export reports',
            'key' => 'reports.export',
        ]);

    $response->assertForbidden();
});
