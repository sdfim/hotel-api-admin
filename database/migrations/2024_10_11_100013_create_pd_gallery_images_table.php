<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('pd_gallery_images', function (Blueprint $table) {
            $table->unsignedBigInteger('gallery_id');
            $table->unsignedBigInteger('image_id');
            $table->timestamps();

            $table->foreign('gallery_id')->references('id')->on('pd_image_galleries')->onDelete('cascade');
            $table->foreign('image_id')->references('id')->on('pd_hotel_images')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_gallery_images');
    }
};
