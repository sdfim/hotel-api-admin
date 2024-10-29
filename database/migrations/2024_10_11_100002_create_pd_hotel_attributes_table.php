<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_hotel_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('attribute_id');

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('config_attributes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotel_attributes');
    }
};
