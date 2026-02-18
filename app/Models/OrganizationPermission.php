<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationPermission extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'key',
    ];

    /**
     * Get the organization that owns the permission.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
