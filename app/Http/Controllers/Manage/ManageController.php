<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\StoreMemberRequest;
use App\Http\Requests\Manage\StorePermissionRequest;
use App\Http\Requests\Manage\UpdateMemberRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ManageController extends Controller
{
    /**
     * Redirect /manage to /manage/users.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('manage.users');
    }

    /**
     * Get current organization for the authenticated user.
     */
    private function currentOrganization(Request $request): ?Organization
    {
        $organizationId = session('current_organization_id');
        if (! $organizationId) {
            $organizationId = $request->user()?->organizations()->first()?->id;
        }

        if (! $organizationId) {
            return null;
        }

        $org = Organization::find($organizationId);
        if (! $org) {
            return null;
        }

        $user = $request->user();
        if ($user?->isSuperadmin()) {
            return $org;
        }

        if (! $user?->belongsToOrganization($org)) {
            return null;
        }

        return $org;
    }

    /**
     * Manage Users - list organization members.
     */
    public function users(Request $request): Response|RedirectResponse
    {
        $organization = $this->currentOrganization($request);
        if (! $organization) {
            return redirect()->route('dashboard');
        }

        $members = $organization->users()
            ->withPivot('role', 'is_active', 'joined_at')
            ->orderByPivot('joined_at', 'desc')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'is_active' => (bool) $user->pivot->is_active,
                'joined_at' => $user->pivot->joined_at,
            ]);

        $plan = $organization->currentPlan();
        $memberLimit = $plan?->member_limit;
        $canAddMore = $request->user()->isSuperadmin()
            || $memberLimit === null
            || $members->count() < $memberLimit;
        $canManageMembers = $request->user()->isSuperadmin()
            || $request->user()->hasRoleInOrganization($organization, 'owner')
            || $request->user()->hasRoleInOrganization($organization, 'admin');

        return Inertia::render('manage/Users', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name],
            'members' => $members,
            'memberLimit' => $memberLimit,
            'canAddMore' => $canAddMore,
            'canManageMembers' => $canManageMembers,
        ]);
    }

    /**
     * Add a team member (admin/owner only). Invites by email; creates user if needed and sends reset link.
     */
    public function storeMember(StoreMemberRequest $request): RedirectResponse
    {
        $organization = $this->currentOrganization($request);
        if (! $organization) {
            return redirect()->route('manage.users')->with('error', 'Organization not found.');
        }

        $plan = $organization->currentPlan();
        $memberLimit = $plan?->member_limit;
        $currentCount = $organization->users()->wherePivot('is_active', true)->count();
        if ($memberLimit !== null && $currentCount >= $memberLimit) {
            return redirect()->route('manage.users')->with('error', 'Member limit reached for your plan.');
        }

        $email = $request->validated('email');
        $role = $request->validated('role');
        $name = $request->validated('name') ?: Str::before($email, '@');

        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->belongsToOrganization($organization)) {
                return redirect()->route('manage.users')->with('error', 'This user is already a member of the organization.');
            }
            $organization->users()->attach($user->id, [
                'role' => $role,
                'is_active' => true,
                'joined_at' => now(),
            ]);

            return redirect()->route('manage.users')->with('success', 'Team member added successfully.');
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
        ]);

        $organization->users()->attach($user->id, [
            'role' => $role,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        Password::sendResetLink(['email' => $email]);

        return redirect()->route('manage.users')->with('success', 'Team member created. They will receive an email to set their password.');
    }

    /**
     * Update a member's role (admin/owner only).
     */
    public function updateMember(UpdateMemberRequest $request, User $user): RedirectResponse
    {
        $organization = $this->currentOrganization($request);
        if (! $organization) {
            return redirect()->route('manage.users')->with('error', 'Organization not found.');
        }

        if (! $user->belongsToOrganization($organization)) {
            return redirect()->route('manage.users')->with('error', 'User is not a member of this organization.');
        }

        $organization->users()->updateExistingPivot($user->id, [
            'role' => $request->validated('role'),
        ]);

        return redirect()->route('manage.users')->with('success', 'Member role updated successfully.');
    }

    /**
     * Remove a member from the organization (admin/owner only).
     */
    public function destroyMember(Request $request, User $user): RedirectResponse
    {
        $organization = $this->currentOrganization($request);
        if (! $organization) {
            return redirect()->route('manage.users')->with('error', 'Organization not found.');
        }

        $authUser = $request->user();
        if (! $authUser->isSuperadmin() && ! $authUser->hasRoleInOrganization($organization, 'owner') && ! $authUser->hasRoleInOrganization($organization, 'admin')) {
            abort(403, 'You are not authorized to remove members.');
        }

        if (! $user->belongsToOrganization($organization)) {
            return redirect()->route('manage.users')->with('error', 'User is not a member of this organization.');
        }

        if (! $authUser->isSuperadmin() && $user->id === $authUser->id) {
            return redirect()->route('manage.users')->with('error', 'You cannot remove yourself.');
        }

        $organization->users()->detach($user->id);

        return redirect()->route('manage.users')->with('success', 'Member removed successfully.');
    }

    /**
     * Manage Memberships - subscription & members overview.
     */
    public function memberships(Request $request): Response|RedirectResponse
    {
        $organization = $this->currentOrganization($request);
        if (! $organization) {
            return redirect()->route('dashboard');
        }

        $subscription = $organization->subscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with('plan')
            ->first();

        $memberCount = $organization->users()->wherePivot('is_active', true)->count();

        return Inertia::render('manage/Memberships', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name],
            'subscription' => $subscription ? [
                'plan_name' => $subscription->plan->name,
                'ends_at' => $subscription->ends_at?->toIso8601String(),
            ] : null,
            'memberCount' => $memberCount,
            'memberLimit' => $subscription?->plan?->member_limit,
        ]);
    }

    /**
     * Permissions - plan modules, role info, and custom permissions for current org.
     */
    public function permissions(Request $request): Response|RedirectResponse
    {
        $organization = $this->currentOrganization($request);
        if (! $organization) {
            return redirect()->route('dashboard');
        }

        $plan = $organization->currentPlan();
        $modules = $plan?->modules ?? collect();
        $roles = [
            ['key' => 'owner', 'name' => 'Owner', 'description' => 'Full access, can delete organization'],
            ['key' => 'admin', 'name' => 'Admin', 'description' => 'Can manage members and settings'],
            ['key' => 'staff', 'name' => 'Staff', 'description' => 'Standard access to enabled modules'],
            ['key' => 'client', 'name' => 'Client', 'description' => 'Limited/read-only access'],
        ];

        $permissions = $organization->permissions()->orderBy('name')->get()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'key' => $p->key,
        ]);

        $canManageOrganization = $request->user()->isSuperadmin()
            || $request->user()->hasRoleInOrganization($organization, 'owner')
            || $request->user()->hasRoleInOrganization($organization, 'admin');

        return Inertia::render('manage/Permissions', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name],
            'plan' => $plan ? ['name' => $plan->name] : null,
            'modules' => $modules->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'slug' => $m->slug]),
            'roles' => $roles,
            'permissions' => $permissions,
            'canManageOrganization' => $canManageOrganization,
        ]);
    }

    /**
     * Store a new custom permission (admin/owner only).
     */
    public function storePermission(StorePermissionRequest $request): RedirectResponse
    {
        $organization = $this->currentOrganization($request);
        if (! $organization) {
            return redirect()->route('manage.permissions')->with('error', 'Organization not found.');
        }

        $organization->permissions()->create($request->validated());

        return redirect()->route('manage.permissions')->with('success', 'Permission created successfully.');
    }
}
