<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_rate_tiers', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_price', 10); // Minimum price in the range
            $table->decimal('max_price', 10); // Maximum price in the range
            $table->decimal('insurance_rate', 5); // Insurance rate in percentage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_rate_tiers');
    }
};
