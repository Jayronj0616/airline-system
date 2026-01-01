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
        Schema::table('seats', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique(['flight_id', 'seat_number']);
            
            // Make seat_number nullable
            $table->string('seat_number', 5)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            // Make seat_number non-nullable again
            $table->string('seat_number', 5)->nullable(false)->change();
            
            // Restore unique constraint
            $table->unique(['flight_id', 'seat_number']);
        });
    }
};
