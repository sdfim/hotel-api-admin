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
            $table->decimal('supplier_fee', 10);
            $table->foreignId('insurance_provider_id')->constrained('insurance_providers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('insurance_plans', function (Blueprint $table) {
            $table->dropForeign(['insurance_provider_id']);
        });

        Schema::dropIfExists('insurance_plans');
    }
};
