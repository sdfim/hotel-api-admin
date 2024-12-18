<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_hotel_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_id');
            $table->string('hbsi_data_mapped_name')->nullable();
            $table->jsonb('supplier_codes')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotel_rooms');
    }
};
