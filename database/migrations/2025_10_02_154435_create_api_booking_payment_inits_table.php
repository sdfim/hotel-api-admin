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
        Schema::create('api_booking_payment_inits', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id', 36); // UUID support
            $table->string('payment_intent_id');
            $table->decimal('amount', 12, 2);
            $table->string('action', 20);
            $table->string('currency', 8);
            $table->string('provider', 64);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_booking_payment_inits');
    }
};
