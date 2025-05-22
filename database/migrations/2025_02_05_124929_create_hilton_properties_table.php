<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('hilton_properties')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('hilton_properties', function (Blueprint $table) {
                $table->id();
                $table->string('prop_code')->unique();
                $table->string('name');
                $table->string('facility_chain_name');
                $table->string('city');
                $table->string('country_code', 10);
                $table->string('address')->nullable();
                $table->string('postal_code')->nullable();
                $table->decimal('latitude', 10, 6)->nullable();
                $table->decimal('longitude', 10, 6)->nullable();
                $table->string('phone_number')->nullable();
                $table->string('email')->nullable();
                $table->string('website')->nullable();
                $table->string('star_rating')->nullable();
                $table->string('market_tier')->nullable();
                $table->integer('year_built')->nullable();
                $table->date('opening_date')->nullable();
                $table->string('time_zone')->nullable();
                $table->time('checkin_time')->nullable();
                $table->time('checkout_time')->nullable();
                $table->boolean('allow_adults_only')->default(false);

                $table->json('props')->nullable();
                $table->json('policy')->nullable();
                $table->json('taxes')->nullable();
                $table->json('meeting_rooms')->nullable();
                $table->json('safety_and_security')->nullable();
                $table->json('location_details')->nullable();
                $table->json('area_locators')->nullable();
                $table->json('nearby_corporations')->nullable();
                $table->json('nearby_points')->nullable();
                $table->json('guest_room_descriptions')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('hilton_properties');
    }
};
