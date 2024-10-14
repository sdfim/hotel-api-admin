<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('booking_item'); // Reference to uuid in api_booking_items table
            $table->decimal('trip_cost_from', 10);
            $table->decimal('trip_cost_to', 10);
            $table->decimal('total_insurance_cost', 10);
            $table->decimal('commission', 10);
            $table->decimal('supplier_cost', 10);
            $table->integer('min_trip_duration')->nullable();
            $table->integer('max_trip_duration')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->foreignId('insurance_provider_id')->constrained('insurance_providers')->onDelete('cascade');
            $table->timestamps();

            $table->foreign('booking_item')->references('booking_item')->on('api_booking_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('insurance_plans', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['insurance_provider_id']);
            $table->dropForeign(['booking_item']);
        });

        Schema::dropIfExists('insurance_plans');
    }
};
