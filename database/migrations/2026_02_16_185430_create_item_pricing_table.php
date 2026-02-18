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
        Schema::create('item_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_price', 15, 4)->nullable();
            $table->decimal('sale_price', 15, 4)->nullable();
            $table->decimal('mrp', 15, 4)->nullable();
            $table->decimal('minimum_sale_price', 15, 4)->nullable();
            $table->decimal('discount_percent_allowed', 8, 2)->nullable();
            $table->decimal('retail_price', 15, 4)->nullable();
            $table->decimal('wholesale_price', 15, 4)->nullable();
            $table->decimal('dealer_price', 15, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pricing');
    }
};
