<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\API\Suppliers\Enums\PropertiesSourceEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('properties')) {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('properties', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->integer('code')->unique()->index();
            $table->timestamp('last_updated')->useCurrent();
            $table->string('name', 191)->default('')->index();
            $table->json('chain')->nullable();
            $table->string('city', 191)->default('')->index();
            $table->integer('city_id')->nullable()->index();
            $table->string('locale', 191)->default('');
            $table->integer('locale_id')->nullable();
            $table->json('address');
            $table->string('mapper_address')->nullable()->index();
            $table->string('mapper_postal_code', 50)->nullable()->index();
            $table->string('mapper_phone_number', 50)->nullable()->index();
            $table->json('phone')->nullable();
            $table->json('position')->nullable();
            $table->double('latitude')->nullable()->index();
            $table->double('longitude')->nullable()->index();
            $table->json('url')->nullable();
            $table->json('cross_references');
            $table->float('rating')->nullable();
            $table->enum('source', [PropertiesSourceEnum::Giata->value, PropertiesSourceEnum::Custom->value])->default(PropertiesSourceEnum::Giata->value);
            $table->unsignedTinyInteger('property_auto_updates')->default(1);
            $table->unsignedTinyInteger('content_auto_updates')->default(1);
            $table->timestamps();

            $table->fullText(['name']);
        });
      }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('properties');
    }
};
