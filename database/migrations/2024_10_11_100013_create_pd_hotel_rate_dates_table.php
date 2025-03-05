<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_rate_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rate_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('rate_id')->references('id')->on('pd_hotel_rates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_rate_dates');
    }
};
