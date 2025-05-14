<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_hotel_crm_mapping', function (Blueprint $table) {
            $table->id();
            $table->string('giata_code')->index();
            $table->unsignedBigInteger('crm_hotel_id')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hotel_mappings');
    }
};
