<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_product_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('config_attribute_id');
            $table->unsignedBigInteger('config_attribute_category_id')->nullable();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
            $table->foreign('config_attribute_id')->references('id')->on('config_attributes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_product_attributes');
    }
};
