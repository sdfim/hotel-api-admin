<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('hotel_trader_content_products')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('hotel_trader_content_products', function (Blueprint $table) {

                $table->id();
                $table->string('hotel_code');
                $table->string('rateplan_code');
                $table->string('roomtype_code');
                $table->json('taxes');
                $table->timestamps();
                $table->unique(['hotel_code', 'rateplan_code', 'roomtype_code'], 'uniq_hotel_rate_room');
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('hotel_trader_content_products');
    }
};

