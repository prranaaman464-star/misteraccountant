<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->isSuperadmin()
            || $user->belongsToOrganization($organization);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create an organization
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->isSuperadmin()
            || $user->hasRoleInOrganization($organization, 'owner')
            || $user->hasRoleInOrganization($organization, 'admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->isSuperadmin()
            || $user->hasRoleInOrganization($organization, 'owner');
    }

    /**
     * Determine whether the user can invite members.
     */
    public function inviteMember(User $user, Organization $organization): bool
    {
        return $user->isSuperadmin()
            || $user->hasRoleInOrganization($organization, 'owner')
            || $user->hasRoleInOrganization($organization, 'admin');
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Organization $organization): bool
    {
        return $user->isSuperadmin()
            || $user->hasRoleInOrganization($organization, 'owner')
            || $user->hasRoleInOrganization($organization, 'admin');
    }
}
