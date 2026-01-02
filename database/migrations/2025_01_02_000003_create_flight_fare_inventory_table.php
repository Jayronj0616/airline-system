<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_fare_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->foreignId('fare_class_id')->constrained('fare_classes')->onDelete('cascade');
            $table->integer('total_seats')->default(0);
            $table->integer('available_seats')->default(0);
            $table->integer('booked_seats')->default(0);
            $table->integer('held_seats')->default(0);
            $table->timestamps();
            
            $table->unique(['flight_id', 'fare_class_id']);
            $table->index('flight_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_fare_inventory');
    }
};
