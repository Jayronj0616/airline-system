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
        Schema::create('fare_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fare_class_id')->constrained('fare_classes')->onDelete('cascade');
            $table->boolean('is_refundable')->default(false);
            $table->decimal('refund_fee_percentage', 5, 2)->default(0); // 0-100%
            $table->decimal('change_fee', 10, 2)->default(0); // Flat fee for changes
            $table->unsignedInteger('checked_bags_allowed')->default(0);
            $table->unsignedInteger('bag_weight_limit_kg')->default(0);
            $table->boolean('seat_selection_free')->default(false);
            $table->boolean('priority_boarding')->default(false);
            $table->text('cancellation_policy')->nullable(); // Human-readable text
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fare_rules');
    }
};
