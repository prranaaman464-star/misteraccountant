<?php

namespace App\Models\Scopes;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Only applies when current organization is bound to the container
     * (e.g. by SetCurrentOrganization middleware).
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->bound('current.organization')) {
            return;
        }

        $organization = app('current.organization');

        if (! $organization instanceof Organization) {
            return;
        }

        $builder->where($model->getTable().'.organization_id', $organization->id);
    }
}
