<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_job_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('pd_hotels')->onDelete('cascade');
            $table->foreignId('job_description_id')->constrained('config_job_descriptions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_hotel_job_descriptions');
    }
};
