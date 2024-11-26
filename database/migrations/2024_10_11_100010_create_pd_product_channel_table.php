<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_product_channel', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('pd_products')->onDelete('cascade');
            $table->foreignId('channel_id')->constrained('channels')->onDelete('cascade');
            $table->unique(['product_id', 'channel_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_product_channel');
    }
};
