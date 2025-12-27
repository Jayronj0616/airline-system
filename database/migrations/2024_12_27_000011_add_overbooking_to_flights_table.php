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
        Schema::table('flights', function (Blueprint $table) {
            $table->boolean('overbooking_enabled')->default(false)->after('demand_score');
            $table->decimal('overbooking_percentage', 5, 2)->default(10.00)->after('overbooking_enabled');
            $table->unsignedInteger('overbooked_count')->default(0)->after('overbooking_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropColumn(['overbooking_enabled', 'overbooking_percentage', 'overbooked_count']);
        });
    }
};
