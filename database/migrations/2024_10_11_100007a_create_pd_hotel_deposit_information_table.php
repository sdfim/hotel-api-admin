<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_deposit_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_id');
            $table->integer('days_departure');
            $table->decimal('per_channel', 8, 2)->nullable();
            $table->decimal('per_room', 8, 2)->nullable();
            $table->decimal('per_rate', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_deposit_information');
    }
};
