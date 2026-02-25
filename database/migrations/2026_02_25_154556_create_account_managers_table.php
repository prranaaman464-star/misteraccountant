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
        Schema::create('account_managers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->default('Your Account Manager');
            $table->text('intro')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('phone_2')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_managers');
    }
};
