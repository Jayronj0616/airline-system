<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->default(12.00)->after('base_price_first');
            $table->decimal('booking_fee', 8, 2)->default(50.00)->after('tax_percentage');
            $table->decimal('fuel_surcharge', 8, 2)->default(100.00)->after('booking_fee');
        });
    }

    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropColumn(['tax_percentage', 'booking_fee', 'fuel_surcharge']);
        });
    }
};
