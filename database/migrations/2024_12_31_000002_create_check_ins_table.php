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
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('passenger_id')->constrained()->onDelete('cascade');
            $table->timestamp('checked_in_at');
            $table->string('check_in_method')->default('online'); // online, airport, kiosk
            $table->timestamps();

            $table->unique(['booking_id', 'passenger_id']);
            $table->index('checked_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
