<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_product_descriptive_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('content_sections_id');
            $table->unsignedBigInteger('descriptive_type_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('content_sections_id')->references('id')->on('pd_product_descriptive_content_sections')->onDelete('cascade');
            $table->foreign('descriptive_type_id')->references('id')->on('config_descriptive_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_product_descriptive_content');
    }
};
