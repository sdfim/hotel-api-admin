<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_web_finder_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('web_finder_id');
            $table->string('field');
            $table->string('value');
            $table->string('type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_web_finder_units');
    }
};
