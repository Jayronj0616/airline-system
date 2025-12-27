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
        Schema::create('price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->foreignId('fare_class_id')->constrained('fare_classes')->onDelete('restrict');
            $table->decimal('price', 10, 2);
            $table->json('factors')->nullable(); // Store pricing factors: {time_factor: 1.2, inventory_factor: 1.5, demand_factor: 1.1}
            $table->decimal('time_factor', 5, 2)->nullable();
            $table->decimal('inventory_factor', 5, 2)->nullable();
            $table->decimal('demand_factor', 5, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            // Indexes for queries
            $table->index(['flight_id', 'fare_class_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_history');
    }
};
