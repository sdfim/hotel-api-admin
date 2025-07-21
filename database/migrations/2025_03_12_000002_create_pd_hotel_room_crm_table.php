<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_room_crm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('crm_room_id');
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('pd_hotel_rooms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_room_crm');
    }
};
