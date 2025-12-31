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
        Schema::create('booking_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('passenger_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['baggage', 'meal', 'seat_upgrade', 'insurance', 'priority_boarding', 'lounge_access']);
            $table->string('description');
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->index(['booking_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_add_ons');
    }
};
