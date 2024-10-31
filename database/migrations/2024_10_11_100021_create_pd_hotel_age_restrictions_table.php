<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_age_restrictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('restriction_type_id');
            $table->integer('value');
            $table->boolean('active');
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
            $table->foreign('restriction_type_id')->references('id')->on('pd_hotel_age_restriction_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_age_restrictions');
    }
};
