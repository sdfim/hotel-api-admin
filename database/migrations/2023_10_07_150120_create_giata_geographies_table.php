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
        if (!Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->hasTable('giata_geographies')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->create('giata_geographies', function (Blueprint $table) {
                $table->id();
                $table->integer('city_id');
                $table->string('city_name');
                $table->integer('locale_id');
                $table->string('locale_name');
                $table->string('country_code');
                $table->string('country_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->dropIfExists('giata_geographies');
    }
};
