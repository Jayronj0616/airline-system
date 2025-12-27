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
        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->string('model', 100); // e.g., "Boeing 737-800"
            $table->string('code', 20)->unique(); // e.g., "B738"
            $table->unsignedInteger('total_seats'); // Total physical seats
            $table->unsignedInteger('economy_seats');
            $table->unsignedInteger('business_seats');
            $table->unsignedInteger('first_class_seats');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aircraft');
    }
};
