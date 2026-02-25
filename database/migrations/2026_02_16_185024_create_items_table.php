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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('item_code', 100)->nullable();
            $table->string('item_category', 100)->nullable();
            $table->string('sub_category', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model_no', 100)->nullable();
            $table->text('description')->nullable();

            $table->boolean('gst_applicable')->default(false);
            $table->string('hsn_sac_code', 50)->nullable();
            $table->decimal('gst_rate', 8, 2)->nullable();
            $table->decimal('cgst_rate', 8, 2)->nullable();
            $table->decimal('sgst_rate', 8, 2)->nullable();
            $table->decimal('igst_rate', 8, 2)->nullable();
            $table->decimal('cess_rate', 8, 2)->nullable();

            $table->decimal('purchase_price', 15, 4)->nullable();
            $table->decimal('sale_price', 15, 4)->nullable();
            $table->decimal('mrp', 15, 4)->nullable();
            $table->decimal('minimum_sale_price', 15, 4)->nullable();
            $table->decimal('discount_percent_allowed', 8, 2)->nullable();
            $table->boolean('price_inclusive_of_tax')->default(false);
            $table->decimal('retail_price', 15, 4)->nullable();
            $table->decimal('wholesale_price', 15, 4)->nullable();
            $table->decimal('dealer_price', 15, 4)->nullable();

            $table->string('primary_unit', 50)->nullable();
            $table->decimal('conversion_factor', 15, 4)->nullable();
            $table->decimal('opening_stock_quantity', 15, 4)->nullable();
            $table->decimal('opening_stock_value', 15, 4)->nullable();
            $table->decimal('stock_quantity', 15, 4)->nullable();
            $table->decimal('reorder_level', 15, 4)->nullable();
            $table->decimal('minimum_stock_level', 15, 4)->nullable();

            $table->boolean('batch_enabled')->default(false);
            $table->boolean('expiry_date_tracking')->default(false);
            $table->boolean('serial_number_tracking')->default(false);
            $table->string('godown_warehouse', 100)->nullable();
            $table->string('item_type', 50);
            $table->string('status', 50);

            $table->string('item_image')->nullable();
            $table->boolean('e_invoice_applicable')->default(false);
            $table->boolean('e_way_bill_applicable')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
