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
        Schema::create('mapper_expedia_giatas', function (Blueprint $table) {
            $table->integer('expedia_id')->index();
            $table->integer('giata_id')->index();

            $table->index(['expedia_id', 'giata_id'], 'idx_expedia_giatas');
            $table->primary(['expedia_id', 'giata_id']);

            $table->integer('step');
        });

        Schema::create('mapper_hbsi_giatas', function (Blueprint $table) {
            $table->string('hbsi_id')->index();
            $table->integer('giata_id')->index();

            $table->index(['hbsi_id', 'giata_id'], 'idx_hbsi_giatas');
            $table->primary(['hbsi_id', 'giata_id']);

            $table->integer('perc');
        });

        Schema::create('mapper_ice_portal_giatas', function (Blueprint $table) {
            $table->integer('ice_portal_id')->index();
            $table->integer('giata_id')->index();

            $table->index(['ice_portal_id', 'giata_id'], 'idx_ice_portal_giatas');
            $table->primary(['ice_portal_id', 'giata_id']);

            $table->integer('perc');
        });

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

        $schema->dropIfExists('mapper_expedia_giatas');
        $schema->dropIfExists('mapper_hbsi_giatas');
        $schema->dropIfExists('mapper_ice_portal_giatas');
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

        $schema->create('mapper_expedia_giatas', function (Blueprint $table) {
            $table->integer('expedia_id')->index();
            $table->integer('giata_id')->index();

            $table->index(['expedia_id', 'giata_id'], 'idx_expedia_giatas');
            $table->primary(['expedia_id', 'giata_id']);

            $table->integer('step');
        });

        $schema->create('mapper_hbsi_giatas', function (Blueprint $table) {
            $table->string('hbsi_id')->index();
            $table->integer('giata_id')->index();

            $table->index(['hbsi_id', 'giata_id'], 'idx_hbsi_giatas');
            $table->primary(['hbsi_id', 'giata_id']);

            $table->integer('perc');
        });

        $schema->create('mapper_ice_portal_giatas', function (Blueprint $table) {
            $table->integer('ice_portal_id')->index();
            $table->integer('giata_id')->index();

            $table->index(['ice_portal_id', 'giata_id'], 'idx_ice_portal_giatas');
            $table->primary(['ice_portal_id', 'giata_id']);

            $table->integer('perc');
        });

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

        Schema::dropIfExists('mapper_expedia_giatas');
        Schema::dropIfExists('mapper_hbsi_giatas');
        Schema::dropIfExists('mapper_ice_portal_giatas');
        Schema::dropIfExists('giata_pois');
        Schema::dropIfExists('giata_places');
        Schema::dropIfExists('giata_geographies');
    }
};
