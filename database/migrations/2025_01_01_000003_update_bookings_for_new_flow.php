<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Make user_id nullable for guest bookings
            $table->foreignId('user_id')->nullable()->change();
            
            // Add contact information for guest bookings
            $table->string('contact_email')->nullable()->after('booking_reference');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('contact_name')->nullable()->after('contact_phone');
            
            // Update status enum to match new flow
            $table->dropColumn('status');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'confirmed_unpaid', 
                'confirmed_paid',
                'cancelled',
                'expired'
            ])->default('draft')->after('contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn(['contact_email', 'contact_phone', 'contact_name']);
            $table->dropColumn('status');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['held', 'confirmed', 'cancelled', 'expired'])
                ->default('held');
        });
    }
};
