<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_promotion_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_promotion_id')->constrained('pd_hotel_promotions')->onDelete('cascade');
            $table->foreignId('gallery_id')->constrained('pd_image_galleries')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_promotion_gallery');
    }
};
