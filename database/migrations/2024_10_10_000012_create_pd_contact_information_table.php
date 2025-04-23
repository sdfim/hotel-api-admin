<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_contact_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contactable_id');
            $table->string('contactable_type');
            $table->string('first_name', 255);
            $table->string('last_name')->nullable();
            $table->string('job_title', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_contact_information');
    }
};
