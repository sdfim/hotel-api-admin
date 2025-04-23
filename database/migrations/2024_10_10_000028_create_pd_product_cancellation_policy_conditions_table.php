<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_product_cancellation_policy_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_cancellation_policy_id');
            $table->string('field');
            $table->string('compare');
            $table->json('value')->nullable();
            $table->string('value_from')->nullable();
            $table->string('value_to')->nullable();
            $table->timestamps();

            $table->foreign('product_cancellation_policy_id', 'fk_pdс_conditions_pdс_id')
                ->references('id')->on('pd_product_cancellation_policies')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_cancellation_policy_conditions');
    }
};
