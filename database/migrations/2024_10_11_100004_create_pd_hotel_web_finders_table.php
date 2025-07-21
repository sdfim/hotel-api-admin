<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_web_finders', function (Blueprint $table) {
            $table->id();
            $table->string('website')->nullable();
            $table->string('base_url');
            $table->string('finder', 1000);
            $table->string('example', 1000)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_web_finders');
    }
};
