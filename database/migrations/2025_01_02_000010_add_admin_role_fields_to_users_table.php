<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('admin_role', ['super_admin', 'operations', 'finance', 'support'])->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('admin_role');
            $table->timestamp('disabled_at')->nullable()->after('is_active');
            $table->string('disabled_by')->nullable()->after('disabled_at');
            $table->text('disabled_reason')->nullable()->after('disabled_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_role', 'is_active', 'disabled_at', 'disabled_by', 'disabled_reason']);
        });
    }
};
