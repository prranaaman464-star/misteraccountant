<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemInventory extends Model
{
    protected $table = 'item_inventory';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'primary_unit',
        'conversion_factor',
        'opening_stock_quantity',
        'opening_stock_value',
        'stock_quantity',
        'reorder_level',
        'minimum_stock_level',
        'batch_enabled',
        'expiry_date_tracking',
        'serial_number_tracking',
        'godown_warehouse',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'batch_enabled' => 'boolean',
            'expiry_date_tracking' => 'boolean',
            'serial_number_tracking' => 'boolean',
            'conversion_factor' => 'decimal:4',
            'opening_stock_quantity' => 'decimal:4',
            'opening_stock_value' => 'decimal:4',
            'stock_quantity' => 'decimal:4',
            'reorder_level' => 'decimal:4',
            'minimum_stock_level' => 'decimal:4',
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
