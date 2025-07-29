<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('hotel_trader_content_hotels')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('hotel_trader_content_hotels', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->json('mapping_providers')->nullable();
                $table->string('name');
                $table->float('star_rating')->nullable();
                $table->string('default_currency_code')->nullable();
                $table->integer('max_rooms_bookable')->nullable();
                $table->integer('number_of_rooms')->nullable();
                $table->integer('number_of_floors')->nullable();
                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('city');
                $table->string('state')->nullable();
                $table->string('state_code')->nullable();
                $table->string('country');
                $table->string('country_code');
                $table->string('zip')->nullable();
                $table->string('phone_1')->nullable();
                $table->string('phone_2')->nullable();
                $table->string('fax_1')->nullable();
                $table->string('fax_2')->nullable();
                $table->string('website_url')->nullable();
                $table->string('longitude')->nullable();
                $table->string('latitude')->nullable();
                $table->text('long_description')->nullable();
                $table->text('short_description')->nullable();
                $table->string('check_in_time')->nullable();
                $table->string('check_out_time')->nullable();
                $table->string('time_zone')->nullable();
                $table->integer('adult_age')->nullable();
                $table->string('default_language')->nullable();
                $table->boolean('adult_only')->default(false);
                $table->json('currencies')->nullable();
                $table->json('languages')->nullable();
                $table->json('credit_card_types')->nullable();
                $table->json('bed_types')->nullable();
                $table->json('amenities')->nullable();
                $table->json('age_categories')->nullable();
                $table->text('check_in_policy')->nullable();
                $table->json('images')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('hotel_trader_content_hotels');
    }
};
