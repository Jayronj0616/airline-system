<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->enum('status', ['available', 'held', 'booked', 'blocked_crew', 'blocked_maintenance'])
                ->default('available')
                ->change();
            $table->string('block_reason')->nullable()->after('hold_expires_at');
            $table->timestamp('blocked_at')->nullable()->after('block_reason');
            $table->foreignId('blocked_by')->nullable()->after('blocked_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->dropColumn(['block_reason', 'blocked_at', 'blocked_by']);
            $table->enum('status', ['available', 'held', 'booked'])
                ->default('available')
                ->change();
        });
    }
};
