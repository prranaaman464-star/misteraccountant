<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;

class OrganizationPermissionService
{
    /**
     * Check if user has access to organization.
     */
    public function userHasAccess(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    /**
     * Check if organization has active subscription.
     */
    public function hasActiveSubscription(Organization $organization): bool
    {
        return $organization->hasActiveSubscription();
    }

    /**
     * Check if organization has access to a module.
     */
    public function hasModuleAccess(Organization $organization, string $moduleSlug): bool
    {
        $plan = $organization->currentPlan();

        if (! $plan) {
            return false;
        }

        return $plan->hasModule($moduleSlug);
    }

    /**
     * Check if organization has reached member limit.
     */
    public function hasReachedMemberLimit(Organization $organization): bool
    {
        $plan = $organization->currentPlan();

        if (! $plan) {
            return true; // No plan means limit reached
        }

        $memberLimit = $plan->member_limit;

        if ($memberLimit === null) {
            return false; // Unlimited
        }

        $currentMemberCount = $organization->users()->wherePivot('is_active', true)->count();

        return $currentMemberCount >= $memberLimit;
    }

    /**
     * Check if organization can add more members.
     */
    public function canAddMember(Organization $organization): bool
    {
        return ! $this->hasReachedMemberLimit($organization);
    }

    /**
     * Check feature limit.
     */
    public function checkFeatureLimit(Organization $organization, string $featureKey, int $currentCount): bool
    {
        $plan = $organization->currentPlan();

        if (! $plan) {
            return false;
        }

        $limit = $plan->getFeatureLimit($featureKey);

        if ($limit === null) {
            return true; // Unlimited
        }

        return $currentCount < $limit;
    }

    /**
     * Get remaining feature limit.
     */
    public function getRemainingFeatureLimit(Organization $organization, string $featureKey, int $currentCount): ?int
    {
        $plan = $organization->currentPlan();

        if (! $plan) {
            return null;
        }

        $limit = $plan->getFeatureLimit($featureKey);

        if ($limit === null) {
            return null; // Unlimited
        }

        return max(0, $limit - $currentCount);
    }
}
