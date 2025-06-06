<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deposit_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('giata_code');
            $table->unsignedBigInteger('rate_id')->nullable();
            $table->string('name');
            $table->dateTime('start_date');
            $table->dateTime('expiration_date');
            $table->string('manipulable_price_type');
            $table->decimal('price_value', 8, 2);
            $table->string('price_value_type');
            $table->string('price_value_target');
            $table->integer('days_after_booking_initial_payment_due')->nullable();
            $table->integer('days_before_arrival_initial_payment_due')->nullable();
            $table->string('initial_payment_due_type')->nullable();
            $table->integer('days_initial_payment_due')->nullable();
            $table->date('date_initial_payment_due')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposit_information');
    }
};
