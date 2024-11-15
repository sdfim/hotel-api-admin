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
            $table->float('min_trip_cost');
            $table->float('max_trip_cost');
            $table->float('consumer_plan_cost');
            $table->float('ujv_retention');
            $table->float('net_to_trip_mate');
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
