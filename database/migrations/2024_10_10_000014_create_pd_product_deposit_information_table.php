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
            $table->string('days_prior_type');
            $table->integer('days')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('pricing_parameters');
            $table->decimal('pricing_value', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_deposit_information');
    }
};
