<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $table = 'sales_invoices';

    protected $fillable = [
        'organization_id',
        'client_id',
        'invoice_number',
        'reference_number',
        'invoice_date',
        'due_date',
        'status',
        'currency',
        'enable_tax',
        'billed_by',
        'round_off_total',
        'discount_percent',
        'subtotal_amount',
        'cgst_amount',
        'sgst_amount',
        'discount_amount',
        'total_amount',
        'additional_notes',
        'terms_and_conditions',
        'account_id',
        'selected_signature_id',
        'signature_name',
        'is_recurring',
        'recurring_frequency',
        'recurring_interval',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'enable_tax' => 'boolean',
            'round_off_total' => 'boolean',
            'is_recurring' => 'boolean',
            'subtotal_amount' => 'decimal:4',
            'cgst_amount' => 'decimal:4',
            'sgst_amount' => 'decimal:4',
            'discount_amount' => 'decimal:4',
            'total_amount' => 'decimal:4',
            'discount_percent' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function billedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'billed_by');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class, 'sales_invoice_id');
    }
}
