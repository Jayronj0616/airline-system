<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add timezone columns to flights table
        Schema::table('flights', function (Blueprint $table) {
            $table->string('origin_timezone', 50)->default('UTC')->after('origin');
            $table->string('destination_timezone', 50)->default('UTC')->after('destination');
        });

        // Convert existing datetime columns to store UTC
        // Note: This assumes existing times are in system timezone
        // In production, you'd need to convert based on actual airport timezones
        DB::statement('ALTER TABLE flights MODIFY departure_time TIMESTAMP NULL');
        DB::statement('ALTER TABLE flights MODIFY arrival_time TIMESTAMP NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropColumn(['origin_timezone', 'destination_timezone']);
        });
    }
};
