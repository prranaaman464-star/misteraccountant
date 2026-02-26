<?php

namespace App\Concerns;

use App\Models\Organization;
use App\Services\OrganizationPermissionService;
use Illuminate\Http\Request;

trait ChecksOrganizationPermissions
{
    /**
     * Get the current organization from request.
     */
    protected function getCurrentOrganization(Request $request): ?Organization
    {
        $organizationId = $request->header('X-Organization-Id')
            ?? $request->query('organization_id')
            ?? $request->route('organization');

        if (! $organizationId) {
            return null;
        }

        return Organization::find($organizationId);
    }

    /**
     * Ensure user has access to organization.
     */
    protected function ensureOrganizationAccess(Request $request, Organization $organization): void
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        if (! $user->isSuperadmin() && ! $user->belongsToOrganization($organization)) {
            abort(403, 'You do not have access to this organization.');
        }
    }

    /**
     * Ensure organization has active subscription.
     */
    protected function ensureActiveSubscription(Organization $organization): void
    {
        $service = app(OrganizationPermissionService::class);

        if (! $service->hasActiveSubscription($organization)) {
            abort(402, 'This organization does not have an active subscription.');
        }
    }

    /**
     * Ensure organization has module access.
     */
    protected function ensureModuleAccess(Organization $organization, string $moduleSlug): void
    {
        $service = app(OrganizationPermissionService::class);

        if (! $service->hasModuleAccess($organization, $moduleSlug)) {
            abort(402, "The '{$moduleSlug}' module is not available in your current plan.");
        }
    }

    /**
     * Check if organization can add member.
     */
    protected function ensureCanAddMember(Organization $organization): void
    {
        $service = app(OrganizationPermissionService::class);

        if (! $service->canAddMember($organization)) {
            abort(402, 'Member limit reached. Please upgrade your plan to add more members.');
        }
    }

    /**
     * Check feature limit.
     */
    protected function ensureFeatureLimit(Organization $organization, string $featureKey, int $currentCount): void
    {
        $service = app(OrganizationPermissionService::class);

        if (! $service->checkFeatureLimit($organization, $featureKey, $currentCount)) {
            $limit = $organization->currentPlan()?->getFeatureLimit($featureKey);
            abort(402, "Feature limit reached. Your plan allows {$limit} {$featureKey}.");
        }
    }
}
