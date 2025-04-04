<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_rate_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_rate_id')->constrained('pd_hotel_rates')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('pd_hotel_rooms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_rate_rooms');
    }
};
