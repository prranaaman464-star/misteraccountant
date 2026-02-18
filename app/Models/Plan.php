<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'member_limit',
        'price',
        'billing_cycle',
        'is_active',
        'sort_order',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'member_limit' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'features' => 'array',
        ];
    }

    /**
     * Get the modules enabled for this plan.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'plan_modules')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }

    /**
     * Get the feature limits for this plan.
     */
    public function featureLimits(): HasMany
    {
        return $this->hasMany(FeatureLimit::class);
    }

    /**
     * Get subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if a module is enabled for this plan.
     */
    public function hasModule(string $moduleSlug): bool
    {
        return $this->modules()
            ->where('modules.slug', $moduleSlug)
            ->wherePivot('is_enabled', true)
            ->exists();
    }

    /**
     * Get feature limit value.
     */
    public function getFeatureLimit(string $featureKey): ?int
    {
        $limit = $this->featureLimits()
            ->where('feature_key', $featureKey)
            ->first();

        return $limit?->limit_value;
    }
}
