<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCompliance extends Model
{
    protected $table = 'item_compliance';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'e_invoice_applicable',
        'e_way_bill_applicable',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'e_invoice_applicable' => 'boolean',
            'e_way_bill_applicable' => 'boolean',
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
