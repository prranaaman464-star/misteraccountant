<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'item_code',
        'category_id',
        'sub_category',
        'brand',
        'model_no',
        'description',
        'item_type',
        'status',
        'item_image',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasOne<ItemTaxDetail, $this>
     */
    public function taxDetail(): HasOne
    {
        return $this->hasOne(ItemTaxDetail::class);
    }

    /**
     * @return HasOne<ItemPricing, $this>
     */
    public function pricing(): HasOne
    {
        return $this->hasOne(ItemPricing::class);
    }

    /**
     * @return HasOne<ItemInventory, $this>
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(ItemInventory::class);
    }

    /**
     * @return HasOne<ItemCompliance, $this>
     */
    public function compliance(): HasOne
    {
        return $this->hasOne(ItemCompliance::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
