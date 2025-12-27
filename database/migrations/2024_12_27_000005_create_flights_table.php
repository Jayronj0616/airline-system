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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number', 10)->unique(); // e.g., "AA101"
            $table->foreignId('aircraft_id')->constrained('aircraft')->onDelete('restrict');
            $table->string('origin', 3); // Airport code (e.g., "LAX")
            $table->string('destination', 3); // Airport code (e.g., "JFK")
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->enum('status', ['scheduled', 'boarding', 'departed', 'arrived', 'cancelled'])->default('scheduled');
            $table->unsignedInteger('base_price_economy')->default(100); // Base price in dollars
            $table->unsignedInteger('base_price_business')->default(300);
            $table->unsignedInteger('base_price_first')->default(800);
            $table->decimal('demand_score', 5, 2)->default(50); // 0-100, affects pricing
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['origin', 'destination', 'departure_time']);
            $table->index('departure_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
