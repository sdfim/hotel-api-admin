<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::create('expedia_content_slaves', function (Blueprint $table) {
            // $table->id();
			$table->integer('property_id')->index();
			$table->unsignedBigInteger('country_hash')->index();
			$table->string('phone')->default('');
			$table->string('fax')->default('');
			$table->string('tax_id')->default('');
			$table->json('category');
			$table->json('business_model');
			$table->string('rank')->default('');
			$table->json('checkin');
			$table->json('checkout');
			$table->json('fees');
			$table->json('policies');
			$table->json('attributes');
			$table->json('amenities');
			$table->json('images');
			$table->json('onsite_payments');
			$table->json('rooms');
			$table->json('rates');
			$table->json('dates');
			$table->json('descriptions');
			$table->json('themes');
			$table->json('chain');
			$table->json('brand');
			$table->json('statistics');
			$table->boolean('multi_unit')->default(false);
			$table->boolean('payment_registration_recommended')->default(false);
			$table->json('vacation_rental_details');
			$table->json('airports');
			$table->json('spoken_languages');
			$table->string('supply_source')->default('expedia');
			$table->json('all_inclusive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedia_content_slaves');
    }
};
