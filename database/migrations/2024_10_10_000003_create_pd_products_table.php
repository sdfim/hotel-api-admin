<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('product_type');
            $table->string('name');
            $table->boolean('verified');
            $table->unsignedBigInteger('content_source_id');
            $table->unsignedBigInteger('property_images_source_id');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('default_currency', 10);
            $table->string('website')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('pd_vendors');
            $table->foreign('content_source_id')->references('id')->on('pd_content_sources');
            $table->foreign('property_images_source_id')->references('id')->on('pd_content_sources');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_products');
    }
};
