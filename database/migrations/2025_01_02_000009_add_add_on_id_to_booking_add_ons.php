<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_add_ons', function (Blueprint $table) {
            $table->foreignId('add_on_id')->nullable()->after('passenger_id')->constrained('add_ons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('booking_add_ons', function (Blueprint $table) {
            $table->dropForeign(['add_on_id']);
            $table->dropColumn('add_on_id');
        });
    }
};
