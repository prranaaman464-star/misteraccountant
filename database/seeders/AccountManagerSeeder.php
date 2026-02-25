<?php

namespace Database\Seeders;

use App\Models\AccountManager;
use Illuminate\Database\Seeder;

class AccountManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (AccountManager::query()->exists()) {
            return;
        }

        AccountManager::query()->create([
            'name' => 'Aman',
            'role' => 'Your Account Manager',
            'intro' => "I've helped 300+ businesses streamline over 20+ countries in last 20 days.",
            'email' => 'aman@misteraccountant.com',
            'phone' => '+919999999999',
            'phone_2' => null,
            'whatsapp' => '+919999999999',
            'is_active' => true,
        ]);
    }
}
