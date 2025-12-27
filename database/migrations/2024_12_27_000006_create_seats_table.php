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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->foreignId('fare_class_id')->constrained('fare_classes')->onDelete('restrict');
            $table->string('seat_number', 5); // e.g., "12A", "15C"
            $table->enum('status', ['available', 'held', 'booked'])->default('available');
            $table->timestamp('held_at')->nullable();
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamps();
            
            // Unique constraint: one seat number per flight
            $table->unique(['flight_id', 'seat_number']);
            
            // Indexes for queries
            $table->index(['flight_id', 'fare_class_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
