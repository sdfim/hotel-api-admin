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
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('giata_properties')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('giata_properties', function (Blueprint $table) {
                $table->integer('code')->index()->unique();
                $table->timestamp('last_updated');
                $table->string('name')->default('')->index();
                $table->json('chain')->nullable();
                $table->string('city')->default('')->index();
                $table->integer('city_id')->nullable()->index();
                $table->string('locale')->default('');
                $table->integer('locale_id')->nullable();
                $table->json('address');
                $table->string('mapper_address', 255)->nullable()->index();
                $table->string('mapper_postal_code', 50)->nullable()->index();
                $table->string('mapper_phone_number', 50)->nullable()->index();
                $table->json('phone')->nullable();
                $table->json('position')->nullable();
                $table->float('latitude', 15, 12)->nullable()->index();
                $table->float('longitude', 15, 12)->nullable()->index();
                $table->json('url')->nullable();
                $table->json('cross_references');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('giata_properties');
    }
};
