<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('property_locations')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('property_locations', function (Blueprint $table) {
                $table->integer('property_code')->primary();
                $table->geography('location', subtype: 'point', srid: 4326);
                $table->spatialIndex('location');

                $table->foreign('property_code')->references('code')->on('properties')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('property_locations');
    }
};
