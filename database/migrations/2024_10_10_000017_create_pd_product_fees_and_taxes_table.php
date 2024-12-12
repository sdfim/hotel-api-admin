<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_product_fees_and_taxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->string('fee_category', 50);
            $table->decimal('net_value', 12, 4);
            $table->decimal('rack_value', 12, 4);
            $table->string('type');
            $table->string('value_type');
            $table->string('apply_type');
            $table->boolean('commissionable');
            $table->string('collected_by');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_product_fees_and_taxes');
    }
};
