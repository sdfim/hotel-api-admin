<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_product_promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('rate_id')->nullable();
            $table->string('promotion_name');
            $table->string('rate_code')->nullable();
            $table->text('description')->nullable();
            $table->date('validity_start');
            $table->date('validity_end')->nullable();
            $table->date('booking_start');
            $table->date('booking_end');
            $table->integer('min_night_stay')->nullable();
            $table->integer('max_night_stay')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('exclusions')->nullable();
            $table->boolean('not_refundable')->default(false);
            $table->boolean('package')->default(false);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_product_promotions');
    }
};
