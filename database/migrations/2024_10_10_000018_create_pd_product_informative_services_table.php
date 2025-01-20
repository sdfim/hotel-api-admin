<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_product_informative_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('service_id');
            $table->decimal('cost', 8, 2);
            $table->string('name');
            $table->string('currency');
            $table->time('service_time')->nullable();
            $table->boolean('show_service_on_pdf');
            $table->boolean('show_service_data_on_pdf');
            $table->boolean('commissionable');
            $table->boolean('auto_book');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('pd_products')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('config_service_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_product_informative_services');
    }
};
