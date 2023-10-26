<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->hasTable('expedia_contents')) {
            Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->create('expedia_contents', function (Blueprint $table) {
                $table->integer('property_id')->index()->unique();
                $table->float('rating')->index()->default(0);
                $table->string('name');
                $table->unsignedBigInteger('giata_TTIcode')->default(0)->index();
                $table->string('city')->index();
                $table->string('state_province_code');
                $table->string('state_province_name');
                $table->string('postal_code');
                $table->string('country_code')->index();
                $table->string('latitude');
                $table->string('longitude');
                $table->string('category_name')->index();
                $table->time('checkin_time')->default('00:00:00');
                $table->time('checkout_time')->default('00:00:00');
                $table->json('address');
                $table->json('ratings');
                $table->json('location');
                $table->string('phone');
                $table->string('fax');
                $table->string('tax_id');
                $table->json('category');
                $table->json('business_model');
                $table->string('rank');
                $table->json('checkin');
                $table->json('checkout');
                $table->json('fees');
                $table->json('policies');
                $table->json('attributes');
                $table->json('amenities');
                $table->json('images');
                $table->json('onsite_payments');
                $table->json('rooms');
                $table->string('total_occupancy');
                $table->json('rooms_occupancy');
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
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->dropIfExists('expedia_contents');
    }
};
