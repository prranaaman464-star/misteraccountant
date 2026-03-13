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
        Schema::create('sales_invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 15, 4)->default(1);
            $table->string('unit', 50)->default('Pcs');
            $table->decimal('rate', 15, 4)->default(0);
            $table->decimal('discount_percent', 8, 2)->default(0);
            $table->decimal('tax_percent', 8, 2)->default(0);
            $table->decimal('amount', 15, 4)->default(0);
            $table->timestamps();

            $table->index('sales_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_line_items');
    }
};
