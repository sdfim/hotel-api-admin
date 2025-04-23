<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_product_deposit_information_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_deposit_information_id');
            $table->string('field');
            $table->string('compare');
            $table->json('value')->nullable();
            $table->string('value_from')->nullable();
            $table->string('value_to')->nullable();
            $table->timestamps();

            $table->foreign('product_deposit_information_id', 'fk_pdi_conditions_pdi_id')
                ->references('id')->on('pd_product_deposit_information')
                ->onDelete('cascade');        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_deposit_information_conditions');
    }
};
