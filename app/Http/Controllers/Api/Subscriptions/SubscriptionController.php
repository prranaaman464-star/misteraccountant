<?php

namespace App\Http\Controllers\Api\Subscriptions;

use App\Concerns\ChecksOrganizationPermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Subscriptions\CreateSubscriptionRequest;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    use ChecksOrganizationPermissions;

    /**
     * Create a new subscription.
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $user = $request->user();
        $organization = Organization::findOrFail($request->organization_id);

        $this->ensureOrganizationAccess($request, $organization);

        // Check if user is owner or admin
        if (! $user->isSuperadmin()
            && ! $user->hasRoleInOrganization($organization, 'owner')
            && ! $user->hasRoleInOrganization($organization, 'admin')) {
            return response()->json([
                'message' => 'Only owners and admins can manage subscriptions.',
            ], 403);
        }

        $subscription = DB::transaction(function () use ($request, $organization) {
            // Cancel existing active subscriptions
            $organization->subscriptions()
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            // Create new subscription
            return Subscription::create([
                'organization_id' => $organization->id,
                'plan_id' => $request->plan_id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(), // Default to monthly, adjust based on plan
            ]);
        });

        return response()->json([
            'subscription' => $subscription->load('plan'),
            'message' => 'Subscription activated successfully.',
        ], 201);
    }

    /**
     * Get organization's current subscription.
     */
    public function show(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        $this->ensureOrganizationAccess($request, $organization);

        $subscription = $organization->activeSubscription()->with('plan')->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'No active subscription found.',
            ], 404);
        }

        return response()->json([
            'subscription' => $subscription,
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        $this->ensureOrganizationAccess($request, $organization);

        if (! $user->isSuperadmin()
            && ! $user->hasRoleInOrganization($organization, 'owner')
            && ! $user->hasRoleInOrganization($organization, 'admin')) {
            return response()->json([
                'message' => 'Only owners and admins can cancel subscriptions.',
            ], 403);
        }

        $subscription = $organization->activeSubscription()->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'No active subscription found.',
            ], 404);
        }

        $subscription->cancel();

        return response()->json([
            'message' => 'Subscription cancelled successfully.',
        ]);
    }
}
