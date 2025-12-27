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
            // Add idempotency key for payment operations
            $table->string('idempotency_key', 64)->nullable()->after('booking_reference');
            
            // Add unique constraint to prevent duplicate bookings
            // A user can have only one active hold per flight with the same idempotency key
            $table->unique(['user_id', 'flight_id', 'idempotency_key'], 'unique_user_flight_idempotency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('unique_user_flight_idempotency');
            $table->dropColumn('idempotency_key');
        });
    }
};
