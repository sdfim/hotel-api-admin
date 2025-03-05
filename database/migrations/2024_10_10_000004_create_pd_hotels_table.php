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
            $table->string('sale_type', 50);
            $table->integer('giata_code');
            $table->boolean('featured_flag')->nullable();
            $table->json('address')->nullable();
            $table->decimal('star_rating', 8, 2);
            $table->integer('weight')->nullable();
            $table->boolean('is_not_auto_weight')->nullable();
            $table->integer('num_rooms');
            $table->decimal('travel_agent_commission', 8, 2)->nullable();
            $table->unsignedBigInteger('room_images_source_id')->nullable();
            $table->string('hotel_board_basis')->nullable();
            $table->timestamps();

            $table->foreign('room_images_source_id')->references('id')->on('pd_content_sources');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotels');
    }
};
