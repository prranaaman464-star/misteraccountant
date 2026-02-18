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
        Schema::create('feature_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('feature_key'); // e.g., 'clients_limit', 'invoices_per_month'
            $table->string('feature_name');
            $table->integer('limit_value')->nullable(); // null means unlimited
            $table->string('limit_type')->default('count'); // count, monthly, yearly
            $table->timestamps();

            $table->unique(['plan_id', 'feature_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_limits');
    }
};
