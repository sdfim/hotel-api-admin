<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('hotel_trader_content_rate_plans')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('hotel_trader_content_rate_plans', function (Blueprint $table) {
                $table->id();
                $table->string('hotel_code');
                $table->string('code');
                $table->string('name');
                $table->json('currency');
                $table->string('short_description');
                $table->text('detail_description')->nullable();
                $table->string('cancellation_policy_code')->nullable();
                $table->json('mealplan')->nullable();
                $table->boolean('is_tax_inclusive')->default(false);
                $table->boolean('is_refundable')->default(false);
                $table->json('rateplan_type')->nullable();
                $table->boolean('is_promo')->default(false);
                $table->json('destination_exclusive')->nullable();
                $table->json('destination_restriction')->nullable();
                $table->json('seasonal_policies')->nullable();
                $table->timestamps();
                $table->unique(['hotel_code', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('hotel_trader_content_rate_plans');
    }
};

