<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('booking_item');
            $table->decimal('total_insurance_cost', 10);
            $table->decimal('commission_ujv', 10);
            $table->decimal('insurance_provider_fee', 10);
            $table->json('request')->nullable();

            $table->foreignId('insurance_provider_id')->constrained('insurance_providers')->onDelete('cascade');
            $table->foreign('booking_item')->references('booking_item')->on('api_booking_items')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('insurance_plans', function (Blueprint $table) {
            $table->dropForeign(['insurance_provider_id']);
            $table->dropForeign(['booking_item']);
        });
        Schema::dropIfExists('insurance_plans');
    }
};
