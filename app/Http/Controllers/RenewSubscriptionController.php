<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RenewSubscriptionController extends Controller
{
    /**
     * Renew the current organization's active subscription (extend ends_at).
     * Only owner or admin can renew.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        $organizationId = session('current_organization_id')
            ?? $user->organizations()->first()?->id;

        if (! $organizationId) {
            return redirect()->route('dashboard')->with('error', 'No organization selected.');
        }

        $organization = Organization::find($organizationId);
        if (! $organization || ! $user->belongsToOrganization($organization)) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        if (! $user->hasRoleInOrganization($organization, 'owner')
            && ! $user->hasRoleInOrganization($organization, 'admin')) {
            return redirect()->route('plan.show')->with('error', 'Only owners and admins can renew the plan.');
        }

        $subscription = $organization->subscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with('plan')
            ->first();

        if (! $subscription) {
            return redirect()->route('plan.show')->with('error', 'No active subscription to renew.');
        }

        $plan = $subscription->plan;
        $base = $subscription->ends_at && $subscription->ends_at->isFuture()
            ? $subscription->ends_at
            : now();
        $newEndsAt = strtolower($plan->billing_cycle ?? 'monthly') === 'yearly'
            ? $base->copy()->addYear()
            : $base->copy()->addMonth();

        $subscription->update(['ends_at' => $newEndsAt]);

        return redirect()->route('plan.show')->with('success', 'Plan renewed successfully.');
    }
}
