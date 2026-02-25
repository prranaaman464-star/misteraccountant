<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManager extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role',
        'intro',
        'email',
        'phone',
        'phone_2',
        'whatsapp',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
