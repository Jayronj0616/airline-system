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
        Schema::create('boarding_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('passenger_id')->constrained()->onDelete('cascade');
            $table->string('barcode')->unique();
            $table->string('gate')->nullable();
            $table->time('boarding_time')->nullable();
            $table->string('boarding_group')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'passenger_id']);
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boarding_passes');
    }
};
