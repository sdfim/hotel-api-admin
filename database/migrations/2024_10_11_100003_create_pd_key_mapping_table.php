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
            $table->unsignedBigInteger('hotel_id');
            $table->string('key_id', 255);
            $table->unsignedBigInteger('key_mapping_owner_id');
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
            $table->foreign('key_mapping_owner_id')->references('id')->on('pd_key_mapping_owners');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_key_mapping');
    }
};
