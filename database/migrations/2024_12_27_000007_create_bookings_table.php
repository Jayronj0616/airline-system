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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 10)->unique(); // e.g., "ABC123XYZ"
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('flight_id')->constrained('flights')->onDelete('restrict');
            $table->foreignId('fare_class_id')->constrained('fare_classes')->onDelete('restrict');
            $table->enum('status', ['held', 'confirmed', 'cancelled', 'expired'])->default('held');
            $table->decimal('locked_price', 10, 2); // Price locked at time of hold
            $table->decimal('total_price', 10, 2); // Total price (locked_price * number of seats)
            $table->unsignedInteger('seat_count'); // Number of seats in this booking
            $table->timestamp('held_at')->nullable();
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['flight_id', 'status']);
            $table->index('hold_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
