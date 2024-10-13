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
            $table->text('address');
            $table->integer('star_rating');
            $table->string('website', 255);
            $table->integer('num_rooms');
            $table->boolean('featured');
            $table->string('location', 255);
            $table->enum('content_source', ['IcePortal', 'Expedia', 'Internal']);
            $table->enum('room_images_source', ['IcePortal', 'Expedia', 'Internal']);
            $table->enum('property_images_source', ['IcePortal', 'Expedia', 'Internal']);
            $table->boolean('channel_management');
            $table->string('hotel_board_basis', 255);
            $table->string('default_currency', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotels');
    }
};
