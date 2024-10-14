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
            $table->string('image_url', 255);
            $table->string('tag', 100);
            $table->integer('weight');
            $table->enum('section', ['gallery','hotel','room','promotion','exterior','amenities']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotel_images');
    }
};
