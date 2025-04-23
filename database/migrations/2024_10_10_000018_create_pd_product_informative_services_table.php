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
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('rate_id')->nullable();
            $table->unsignedBigInteger('service_id');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->decimal('cost', 15, 2);
            $table->decimal('total_net', 15, 2)->nullable();
            $table->string('apply_type')->nullable();
            $table->string('name', 2000);
            $table->string('currency');
            $table->time('service_time')->nullable();
            $table->boolean('show_service_on_pdf');
            $table->boolean('show_service_data_on_pdf');
            $table->boolean('commissionable');
            $table->boolean('auto_book');
            $table->unsignedInteger('age_from')->nullable();
            $table->unsignedInteger('age_to')->nullable();
            $table->unsignedInteger('min_night_stay')->nullable();
            $table->unsignedInteger('max_night_stay')->nullable();
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
