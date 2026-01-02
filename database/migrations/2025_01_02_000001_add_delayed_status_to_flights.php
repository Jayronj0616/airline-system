<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'delayed', 'boarding', 'departed', 'arrived', 'cancelled'])
                ->default('scheduled')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'boarding', 'departed', 'arrived', 'cancelled'])
                ->default('scheduled')
                ->change();
        });
    }
};
