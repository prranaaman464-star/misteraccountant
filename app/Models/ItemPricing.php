<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPricing extends Model
{
    protected $table = 'item_pricing';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'purchase_price',
        'sale_price',
        'mrp',
        'minimum_sale_price',
        'discount_percent_allowed',
        'retail_price',
        'wholesale_price',
        'dealer_price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:4',
            'sale_price' => 'decimal:4',
            'mrp' => 'decimal:4',
            'minimum_sale_price' => 'decimal:4',
            'discount_percent_allowed' => 'decimal:2',
            'retail_price' => 'decimal:4',
            'wholesale_price' => 'decimal:4',
            'dealer_price' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
