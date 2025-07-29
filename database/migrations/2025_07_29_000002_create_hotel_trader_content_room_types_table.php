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
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('hotel_trader_content_room_types')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('hotel_trader_content_room_types', function (Blueprint $table) {
                $table->id();
                $table->string('hotel_code');
                $table->string('code');
                $table->string('name');
                $table->text('long_description')->nullable();
                $table->text('short_description')->nullable();
                $table->unsignedTinyInteger('max_adult_occupancy')->nullable();
                $table->unsignedTinyInteger('min_adult_occupancy')->nullable();
                $table->unsignedTinyInteger('max_child_occupancy')->nullable();
                $table->unsignedTinyInteger('min_child_occupancy')->nullable();
                $table->unsignedTinyInteger('total_max_occupancy')->nullable();
                $table->unsignedTinyInteger('max_occupancy_for_default_price')->nullable();
                $table->json('bedtypes')->nullable();
                $table->json('amenities')->nullable();
                $table->json('images')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('hotel_trader_content_room_types');
    }
};
