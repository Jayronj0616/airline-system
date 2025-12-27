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
        Schema::table('fare_rules', function (Blueprint $table) {
            // Add cancellation fee field
            $table->decimal('cancellation_fee', 10, 2)->default(0)->after('change_fee');
            
            // Add seat selection fee (0 = free)
            $table->decimal('seat_selection_fee', 10, 2)->default(0)->after('seat_selection_free');
            
            // Add JSON field for custom rules
            $table->json('rules_json')->nullable()->after('cancellation_policy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fare_rules', function (Blueprint $table) {
            $table->dropColumn(['cancellation_fee', 'seat_selection_fee', 'rules_json']);
        });
    }
};
