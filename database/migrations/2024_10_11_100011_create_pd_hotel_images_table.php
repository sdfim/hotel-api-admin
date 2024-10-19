<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_hotel_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image_url')->index();
            $table->string('tag', 100);
            $table->string('weight');
            $table->unsignedBigInteger('section_id');
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('pd_hotel_image_sections');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotel_images');
    }
};
