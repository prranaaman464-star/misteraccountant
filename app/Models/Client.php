<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'tax_id',
        'status',
        'notes',
        'avatar',
        'currency',
        'website',
        'billing_name',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_pincode',
        'shipping_name',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_pincode',
        'bank_name',
        'branch',
        'account_holder',
        'account_number',
        'ifsc_code',
        'balance',
        'total_invoice',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * Get the organization that owns the client.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
