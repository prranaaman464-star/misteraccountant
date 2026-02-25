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
        Schema::create('item_tax_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->boolean('gst_applicable')->default(false);
            $table->string('hsn_sac_code', 50)->nullable();
            $table->decimal('gst_rate', 8, 2)->nullable();
            $table->decimal('cgst_rate', 8, 2)->nullable();
            $table->decimal('sgst_rate', 8, 2)->nullable();
            $table->decimal('igst_rate', 8, 2)->nullable();
            $table->decimal('cess_rate', 8, 2)->nullable();
            $table->boolean('price_inclusive_of_tax')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_tax_details');
    }
};
