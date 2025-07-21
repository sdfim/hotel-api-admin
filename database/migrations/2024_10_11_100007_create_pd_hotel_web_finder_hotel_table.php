<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_web_finder_hotel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_finder_id')->constrained('pd_hotel_web_finders')->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained('pd_hotels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_web_finder_hotel');
    }
};
