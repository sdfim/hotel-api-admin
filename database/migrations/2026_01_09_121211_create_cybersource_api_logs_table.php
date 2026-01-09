<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cybersource_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->string('payment_intent_id')->nullable();
            $table->string('method_action_id')->nullable();
            $table->json('direction')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->integer('status_code')->nullable();
            $table->uuid('booking_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cybersource_api_logs');
    }
};
