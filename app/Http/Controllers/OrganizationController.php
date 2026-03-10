<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * Show current organization details, plan, and options.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $organizationId = session('current_organization_id');

        if (! $organizationId) {
            $first = $user->organizations()->first();
            $organizationId = $first?->id;
        }

        $organization = $organizationId
            ? $user->organizations()->find($organizationId)
            : null;

        if (! $organization && $user->isSuperadmin() && $organizationId) {
            $organization = Organization::find($organizationId);
        }

        if (! $organization) {
            return redirect()->route('onboarding.organizations.create');
        }

        $subscription = $organization->subscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with('plan.modules', 'plan.featureLimits')
            ->first();

        $plan = $subscription?->plan;
        $memberCount = $organization->users()->wherePivot('is_active', true)->count();

        $canManageOrganization = $user->isSuperadmin()
            || $user->hasRoleInOrganization($organization, 'owner')
            || $user->hasRoleInOrganization($organization, 'admin');

        return Inertia::render('organization/Show', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'address' => $organization->address,
            ],
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at?->toIso8601String(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
            ] : null,
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'member_limit' => $plan->member_limit,
                'price' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'modules' => $plan->modules,
                'feature_limits' => $plan->featureLimits,
            ] : null,
            'member_count' => $memberCount,
            'can_manage_organization' => $canManageOrganization,
        ]);
    }

    /**
     * Update the organization.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $organization->update($request->validated());

        return redirect()->route('organization.show')
            ->with('success', 'Organization updated successfully.');
    }
}
