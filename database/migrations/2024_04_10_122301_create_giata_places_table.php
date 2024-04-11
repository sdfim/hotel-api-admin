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
        if (!Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->hasTable('giata_places')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->create('giata_places', function (Blueprint $table) {
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
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->dropIfExists('giata_places');
    }
};
