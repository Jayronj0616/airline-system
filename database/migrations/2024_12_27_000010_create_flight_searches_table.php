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
        Schema::create('flight_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->date('search_date')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('searched_at');
            $table->timestamps();

            // Indexes for performance
            $table->index('flight_id');
            $table->index('user_id');
            $table->index('searched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_searches');
    }
};
