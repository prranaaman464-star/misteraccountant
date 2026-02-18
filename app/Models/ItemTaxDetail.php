<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemTaxDetail extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'gst_applicable',
        'hsn_sac_code',
        'gst_rate',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'cess_rate',
        'price_inclusive_of_tax',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gst_applicable' => 'boolean',
            'price_inclusive_of_tax' => 'boolean',
            'gst_rate' => 'decimal:2',
            'cgst_rate' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'igst_rate' => 'decimal:2',
            'cess_rate' => 'decimal:2',
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
