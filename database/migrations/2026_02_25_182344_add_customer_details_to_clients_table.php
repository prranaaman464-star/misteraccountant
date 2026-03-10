<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('notes');
            $table->string('currency', 10)->nullable()->after('avatar');
            $table->string('website')->nullable()->after('currency');
            $table->string('billing_name')->nullable()->after('website');
            $table->string('billing_address_line_1')->nullable()->after('billing_name');
            $table->string('billing_address_line_2')->nullable()->after('billing_address_line_1');
            $table->string('billing_country')->nullable()->after('billing_address_line_2');
            $table->string('billing_state')->nullable()->after('billing_country');
            $table->string('billing_city')->nullable()->after('billing_state');
            $table->string('billing_pincode', 20)->nullable()->after('billing_city');
            $table->string('shipping_name')->nullable()->after('billing_pincode');
            $table->string('shipping_address_line_1')->nullable()->after('shipping_name');
            $table->string('shipping_address_line_2')->nullable()->after('shipping_address_line_1');
            $table->string('shipping_country')->nullable()->after('shipping_address_line_2');
            $table->string('shipping_state')->nullable()->after('shipping_country');
            $table->string('shipping_city')->nullable()->after('shipping_state');
            $table->string('shipping_pincode', 20)->nullable()->after('shipping_city');
            $table->string('bank_name')->nullable()->after('shipping_pincode');
            $table->string('branch')->nullable()->after('bank_name');
            $table->string('account_holder')->nullable()->after('branch');
            $table->string('account_number')->nullable()->after('account_holder');
            $table->string('ifsc_code', 20)->nullable()->after('account_number');
            $table->decimal('balance', 15, 2)->default(0)->after('ifsc_code');
            $table->unsignedInteger('total_invoice')->default(0)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'currency', 'website',
                'billing_name', 'billing_address_line_1', 'billing_address_line_2',
                'billing_country', 'billing_state', 'billing_city', 'billing_pincode',
                'shipping_name', 'shipping_address_line_1', 'shipping_address_line_2',
                'shipping_country', 'shipping_state', 'shipping_city', 'shipping_pincode',
                'bank_name', 'branch', 'account_holder', 'account_number', 'ifsc_code',
                'balance', 'total_invoice',
            ]);
        });
    }
};
