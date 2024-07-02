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
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('giata_pois')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('giata_pois', function (Blueprint $table) {
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('giata_pois');
    }
};
