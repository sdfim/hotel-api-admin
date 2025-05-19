<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_room_merges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_room_id');
            $table->unsignedBigInteger('child_room_id');
            $table->unsignedBigInteger('new_room_id'); // Add this line
            $table->json('overwritten_fields')->nullable();
            $table->timestamps();

            $table->foreign('parent_room_id')->references('id')->on('pd_hotel_rooms')->onDelete('cascade');
            $table->foreign('child_room_id')->references('id')->on('pd_hotel_rooms')->onDelete('cascade');
            $table->foreign('new_room_id')->references('id')->on('pd_hotel_rooms')->onDelete('cascade'); // Add this line
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_room_merges');
    }
};
