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
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('rate_id')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedBigInteger('supplier_id');
            $table->string('action_type')->nullable();
            $table->string('old_name')->nullable();
            $table->string('name')->nullable();
            $table->decimal('net_value', 12, 4)->nullable();
            $table->decimal('rack_value', 12, 4)->nullable();
            $table->string('type')->nullable();
            $table->string('value_type')->nullable();
            $table->string('apply_type')->nullable();
            $table->boolean('commissionable')->default(false);
            $table->string('fee_category', 50)->nullable();
            $table->string('collected_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_product_fees_and_taxes');
    }
};
