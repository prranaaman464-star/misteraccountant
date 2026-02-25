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
        Schema::create('item_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_inventory');
    }
};
