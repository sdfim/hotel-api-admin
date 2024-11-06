<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_rate_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_provider_id')->constrained('insurance_providers')->onDelete('cascade');
            $table->decimal('min_price', 10); // Minimum price in the range
            $table->decimal('max_price', 10); // Maximum price in the range
            $table->string('rate_type', 20);
            $table->decimal('rate_value'); // Insurance rate in percentage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('insurance_rate_tiers', function (Blueprint $table) {
            $table->dropForeign(['insurance_provider_id']);
        });
        Schema::dropIfExists('insurance_rate_tiers');
    }
};
