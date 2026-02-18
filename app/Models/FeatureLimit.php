<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureLimit extends Model
{
    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_name',
        'limit_value',
        'limit_type',
    ];

    protected function casts(): array
    {
        return [
            'limit_value' => 'integer',
        ];
    }

    /**
     * Get the plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if limit is unlimited.
     */
    public function isUnlimited(): bool
    {
        return $this->limit_value === null;
    }
}
