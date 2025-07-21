<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_related_room_pivot_table', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('related_room_id');

            $table->foreign('room_id')->references('id')->on('pd_hotel_rooms')->onDelete('cascade');
            $table->foreign('related_room_id')->references('id')->on('pd_hotel_rooms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_related_room_pivot_table');
    }
};
