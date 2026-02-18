<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanDetailController extends Controller
{
    /**
     * Show current organization's plan details: expiry, member limit, usage.
     */
    public function __invoke(Request $request): Response
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

        $subscription = $organization?->subscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with('plan.modules', 'plan.featureLimits')
            ->first();

        $plan = $subscription?->plan;
        $memberCount = $organization ? $organization->users()->wherePivot('is_active', true)->count() : 0;

        return Inertia::render('plan/Show', [
            'organization' => $organization ? [
                'id' => $organization->id,
                'name' => $organization->name,
            ] : null,
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
        ]);
    }
}
