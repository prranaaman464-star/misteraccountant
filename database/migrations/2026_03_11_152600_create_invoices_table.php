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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->string('reference_number')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status', 50)->default('draft');
            $table->string('currency', 10)->default('USD');
            $table->boolean('enable_tax')->default(true);
            $table->foreignId('billed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('round_off_total')->default(true);
            $table->decimal('discount_percent', 8, 2)->default(0);
            $table->decimal('subtotal_amount', 15, 4)->default(0);
            $table->decimal('cgst_amount', 15, 4)->default(0);
            $table->decimal('sgst_amount', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 4)->default(0);
            $table->decimal('total_amount', 15, 4)->default(0);
            $table->text('additional_notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->string('account_id')->nullable();
            $table->string('selected_signature_id')->nullable();
            $table->string('signature_name')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency', 50)->nullable();
            $table->string('recurring_interval', 20)->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'invoice_date']);
            $table->unique(['organization_id', 'invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
