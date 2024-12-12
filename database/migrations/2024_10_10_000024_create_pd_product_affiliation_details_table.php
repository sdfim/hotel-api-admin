<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_product_affiliation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliation_id');
            $table->unsignedBigInteger('consortia_id');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('combinable')->nullable();
            $table->timestamps();

            $table->foreign('affiliation_id')->references('id')->on('pd_product_affiliations')->onDelete('cascade');
            $table->foreign('consortia_id')->references('id')->on('config_consortia')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_affiliation_details');
    }
};
