<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_hotel_promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_id');
            $table->string('promotion_name', 255);
            $table->text('description');
            $table->date('validity_start');
            $table->date('validity_end');
            $table->date('booking_start');
            $table->date('booking_end');
            $table->text('terms_conditions');
            $table->text('exclusions');
            $table->text('deposit_info');
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotel_promotions');
    }
};
