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
        Schema::create('giata_pois', function (Blueprint $table) {
            $table->id();
            $table->string('poi_id')->unique();
            $table->string('name_primary');
            $table->string('type');
            $table->string('country_code');
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->json('places');
            $table->json('name_others')->nullable();
            $table->timestamps();
        });

        Schema::create('giata_places', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('parent_key')->nullable();
            $table->string('name_primary');
            $table->string('type');
            $table->string('state')->nullable();
            $table->string('country_code')->nullable();
            $table->json('airports');
            $table->json('name_others');
            $table->json('tticodes');
            $table->timestamps();
        });

        Schema::create('giata_geographies', function (Blueprint $table) {
            $table->id();
            $table->integer('city_id');
            $table->string('city_name');
            $table->integer('locale_id');
            $table->string('locale_name');
            $table->string('country_code');
            $table->string('country_name');
        });

        $connection = config('database.active_connections.mysql_cache');
        $schema = Schema::connection($connection);

        $schema->dropIfExists('giata_pois');
        $schema->dropIfExists('giata_places');
        $schema->dropIfExists('giata_geographies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('database.active_connections.mysql_cache');
        $schema = Schema::connection($connection);

        $schema->create('giata_pois', function (Blueprint $table) {
            $table->id();
            $table->string('poi_id')->unique();
            $table->string('name_primary');
            $table->string('type');
            $table->string('country_code');
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->json('places');
            $table->json('name_others')->nullable();
            $table->timestamps();
        });

        $schema->create('giata_places', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('parent_key')->nullable();
            $table->string('name_primary');
            $table->string('type');
            $table->string('state')->nullable();
            $table->string('country_code')->nullable();
            $table->json('airports');
            $table->json('name_others');
            $table->json('tticodes');
            $table->timestamps();
        });

        $schema->create('giata_geographies', function (Blueprint $table) {
            $table->id();
            $table->integer('city_id');
            $table->string('city_name');
            $table->integer('locale_id');
            $table->string('locale_name');
            $table->string('country_code');
            $table->string('country_name');
        });

        Schema::dropIfExists('giata_pois');
        Schema::dropIfExists('giata_places');
        Schema::dropIfExists('giata_geographies');
    }
};
