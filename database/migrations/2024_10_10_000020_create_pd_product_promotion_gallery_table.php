<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_product_promotion_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_promotion_id')->constrained('pd_product_promotions')->onDelete('cascade');
            $table->foreignId('gallery_id')->constrained('pd_image_galleries')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_promotion_gallery');
    }
};
