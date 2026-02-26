<?php

namespace App\Models\Concerns;

use App\Models\Scopes\OrganizationScope;

trait BelongsToOrganization
{
    /**
     * Boot the trait and add the global scope.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope);
    }
}
