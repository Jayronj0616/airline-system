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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('passport_number')->nullable()->after('date_of_birth');
            $table->string('nationality')->nullable()->after('passport_number');
            $table->text('address')->nullable()->after('nationality');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('country');
            $table->json('favorite_routes')->nullable()->after('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'date_of_birth',
                'passport_number',
                'nationality',
                'address',
                'city',
                'country',
                'postal_code',
                'favorite_routes',
            ]);
        });
    }
};
