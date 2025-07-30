<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('hotel_trader_content_cancellation_policies')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('hotel_trader_content_cancellation_policies', function (Blueprint $table) {
                $table->id();
                $table->string('hotel_code');
                $table->string('code');
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('penalty_windows')->nullable();
                $table->timestamps();
                $table->unique(['hotel_code', 'code'], 'htccp_hotel_code_code_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('hotel_trader_content_cancellation_policies');
    }
};

