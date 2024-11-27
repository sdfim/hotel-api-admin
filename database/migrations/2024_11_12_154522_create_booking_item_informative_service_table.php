<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_item_informative_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('booking_item');
            $table->decimal('cost', 8, 2);
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('config_service_types')->onDelete('cascade');
            $table->foreign('booking_item')->references('booking_item')->on('api_booking_items')->onDelete('cascade');

            $table->unique(['service_id', 'booking_item']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_item_informative_service');
    }
};
