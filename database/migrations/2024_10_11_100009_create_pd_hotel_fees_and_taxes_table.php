<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_hotel_fees_and_taxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_id');
            $table->string('name');
            $table->string('fee_category', 50);
            $table->decimal('net_value', 10, 2);
            $table->decimal('rack_value', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->string('type');
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotel_fees_and_taxes');
    }
};
