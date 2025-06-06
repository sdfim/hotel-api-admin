<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deposit_information_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('deposit_information_id');
            $table->string('field');
            $table->string('compare');
            $table->json('value')->nullable();
            $table->string('value_from')->nullable();
            $table->string('value_to')->nullable();
            $table->timestamps();

            $table->foreign('deposit_information_id', 'fk_di_conditions_di_id')
                ->references('id')->on('deposit_information')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposit_information_conditions');
    }
};
