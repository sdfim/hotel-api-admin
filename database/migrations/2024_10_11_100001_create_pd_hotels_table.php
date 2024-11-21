<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('sale_type', 50);
            $table->json('address')->nullable();
            $table->integer('star_rating');
            $table->integer('weight')->nullable();
            $table->integer('num_rooms');
            $table->unsignedBigInteger('room_images_source_id');
            $table->string('hotel_board_basis')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
            $table->foreign('room_images_source_id')->references('id')->on('pd_content_sources');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotels');
    }
};
