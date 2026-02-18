<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $organizationId = session('current_organization_id');

        if (! $organizationId) {
            return redirect()->route('onboarding.organizations.create');
        }

        $organization = Organization::find($organizationId);

        if (! $organization || ! $request->user()?->belongsToOrganization($organization)) {
            session()->forget('current_organization_id');

            return redirect()->route('onboarding.organizations.create');
        }

        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['modules', 'featureLimits'])
            ->get();

        return Inertia::render('onboarding/SelectPlan', [
            'organization' => $organization,
            'plans' => $plans,
        ]);
    }

    /**
     * Activate subscription for the organization.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $organizationId = session('current_organization_id');

        if (! $organizationId) {
            return redirect()->route('onboarding.organizations.create')
                ->with('error', 'Please create an organization first.');
        }

        $organization = Organization::find($organizationId);

        if (! $organization || ! $request->user()?->belongsToOrganization($organization)) {
            session()->forget('current_organization_id');

            return redirect()->route('onboarding.organizations.create');
        }

        $plan = Plan::findOrFail($request->plan_id);

        DB::transaction(function () use ($organization, $plan) {
            $organization->subscriptions()
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            Subscription::create([
                'organization_id' => $organization->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Subscription activated successfully.');
    }
}
