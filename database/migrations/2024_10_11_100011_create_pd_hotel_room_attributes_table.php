<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_room_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_room_id')->constrained('pd_hotel_rooms')->onDelete('cascade');
            $table->foreignId('config_attribute_id')->constrained('config_attributes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_room_attributes');
    }
};
