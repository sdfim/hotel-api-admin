<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_hotel_descriptive_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_id');
            $table->string('section_name', 255);
            $table->text('meta_description');
            $table->text('property_description');
            $table->text('cancellation_policy');
            $table->text('pet_policy');
            $table->text('terms_conditions');
            $table->text('fees_paid_at_hotel');
            $table->text('staff_contact_info');
            $table->date('validity_start');
            $table->date('validity_end')->nullable();
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('pd_hotels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_hotel_descriptive_content');
    }
};
