<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('add_on_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('add_on_id')->constrained('add_ons')->onDelete('cascade');
            $table->string('route_origin', 3)->nullable();
            $table->string('route_destination', 3)->nullable();
            $table->foreignId('fare_class_id')->nullable()->constrained('fare_classes')->onDelete('cascade');
            $table->decimal('price_override', 10, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            $table->index(['add_on_id', 'is_available']);
            $table->index(['route_origin', 'route_destination']);
            $table->index('fare_class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('add_on_availability');
    }
};
