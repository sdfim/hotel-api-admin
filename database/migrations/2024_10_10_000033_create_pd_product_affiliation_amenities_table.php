<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_product_affiliation_amenities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_affiliation_id');
            $table->unsignedBigInteger('amenity_id');
            $table->json('consortia')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->decimal('price', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('product_affiliation_id')->references('id')->on('pd_product_affiliations')->onDelete('cascade');
            $table->foreign('amenity_id')->references('id')->on('config_amenities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_product_affiliation_amenities');
    }
};
