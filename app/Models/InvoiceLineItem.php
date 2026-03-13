<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLineItem extends Model
{
    protected $table = 'sales_invoice_line_items';

    protected $fillable = [
        'sales_invoice_id',
        'item_id',
        'product_name',
        'quantity',
        'unit',
        'rate',
        'discount_percent',
        'tax_percent',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'rate' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'amount' => 'decimal:4',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'sales_invoice_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
