<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_product_deposit_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->dateTime('start_date');
            $table->dateTime('expiration_date');
            $table->string('manipulable_price_type');
            $table->decimal('price_value', 8, 2);
            $table->string('price_value_type');
            $table->string('price_value_target');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_deposit_information');
    }
};
