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
        Schema::create('saved_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_type'); // 'card', 'bank', etc.
            $table->string('card_brand')->nullable(); // 'visa', 'mastercard', etc.
            $table->string('last_four'); // Last 4 digits
            $table->string('token'); // Payment gateway token (encrypted)
            $table->string('cardholder_name')->nullable();
            $table->string('expiry_month')->nullable();
            $table->string('expiry_year')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_payment_methods');
    }
};
