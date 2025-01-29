<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_product_descriptive_content_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('rate_id')->nullable();
            $table->string('section_name')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedBigInteger('descriptive_type_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('product_id', 'fk_product_id')->references('id')->on('pd_products')->onDelete('cascade');
            $table->foreign('descriptive_type_id', 'fk_descriptive_type_id')->references('id')->on('config_descriptive_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_descriptive_content_sections');
    }
};
