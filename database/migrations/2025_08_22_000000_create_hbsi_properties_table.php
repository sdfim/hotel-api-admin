<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::connection(config('database.active_connections.mysql_cache'))->hasTable('hbsi_properties')) {
            Schema::connection(config('database.active_connections.mysql_cache'))->create('hbsi_properties', function (Blueprint $table) {
                $table->id();
                $table->string('hotel_code')->index();
                $table->string('hotel_name');
                $table->string('city_code')->nullable();
                $table->string('address_line')->nullable();
                $table->string('city_name')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country_name')->nullable();
                $table->string('phone')->nullable();
                $table->text('emails')->nullable();
                $table->json('rateplans')->nullable();
                $table->json('roomtypes')->nullable();
                $table->json('tpa_extensions')->nullable();
                $table->longText('raw_xml')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection(config('database.active_connections.mysql_cache'))->dropIfExists('hbsi_properties');
    }
};
