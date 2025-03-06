<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_key_mapping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('key_id');
            $table->unsignedBigInteger('key_mapping_owner_id');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
            $table->foreign('key_mapping_owner_id')->references('id')->on('pd_key_mapping_owners')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_key_mapping');
    }
};
