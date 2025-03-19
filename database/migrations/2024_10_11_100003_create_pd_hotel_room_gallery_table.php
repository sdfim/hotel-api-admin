<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_room_gallery', function (Blueprint $table) {
            $table->foreignId('hotel_room_id')->constrained('pd_hotel_rooms')->onDelete('cascade');
            $table->foreignId('gallery_id')->constrained('pd_image_galleries')->onDelete('cascade');
            $table->primary(['hotel_room_id', 'gallery_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_room_gallery');
    }
};
