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
            $table->string('hero_image')->nullable();
            $table->string('hero_image_thumbnails')->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->string('product_type');
            $table->string('name');
            $table->boolean('verified');
            $table->boolean('onSale')->default(false);
            $table->string('on_sale_causation')->nullable();
            $table->unsignedBigInteger('content_source_id');
            $table->unsignedBigInteger('property_images_source_id');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('default_currency', 10);
            $table->string('website', 500)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->string('off_sale_by_sources')->nullable();
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
