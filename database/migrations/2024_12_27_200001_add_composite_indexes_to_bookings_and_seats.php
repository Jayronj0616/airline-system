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
        Schema::table('bookings', function (Blueprint $table) {
            // Composite index for frequent queries filtering by flight, fare class, and status
            $table->index(['flight_id', 'fare_class_id', 'status'], 'idx_bookings_flight_fare_status');
        });

        Schema::table('seats', function (Blueprint $table) {
            // Composite index for queries checking seat availability with expiration
            $table->index(['flight_id', 'status', 'hold_expires_at'], 'idx_seats_flight_status_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_flight_fare_status');
        });

        Schema::table('seats', function (Blueprint $table) {
            $table->dropIndex('idx_seats_flight_status_expires');
        });
    }
};
