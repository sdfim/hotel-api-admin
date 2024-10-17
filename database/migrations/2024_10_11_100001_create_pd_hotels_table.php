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
            $table->string('name', 255);
            $table->string('type', 255);
            $table->boolean('verified');
            $table->boolean('direct_connection');
            $table->boolean('manual_contract');
            $table->boolean('commission_tracking');
            $table->json('address')->nullable();
            $table->integer('star_rating');
            $table->string('website', 255);
            $table->integer('num_rooms');
            $table->boolean('featured');
            $table->json('location')->nullable();
            $table->unsignedBigInteger('content_source_id');
            $table->unsignedBigInteger('room_images_source_id');
            $table->unsignedBigInteger('property_images_source_id');
            $table->boolean('channel_management');
            $table->string('hotel_board_basis', 255);
            $table->string('default_currency', 10);
            $table->timestamps();

            $table->foreign('content_source_id')->references('id')->on('pd_content_sources');
            $table->foreign('room_images_source_id')->references('id')->on('pd_content_sources');
            $table->foreign('property_images_source_id')->references('id')->on('pd_content_sources');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotels');
    }
};
