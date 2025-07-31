<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasTable('hotel_trader_content_taxes')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->create('hotel_trader_content_taxes', function (Blueprint $table) {
                $table->id();
                $table->string('hotel_code');
                $table->string('code');
                $table->string('name');
                $table->string('percent_or_flat');
                $table->string('charge_frequency');
                $table->string('charge_basis');
                $table->float('value');
                $table->string('tax_type')->nullable();
                $table->boolean('applies_to_children')->default(false);
                $table->boolean('pay_at_property')->default(false);
                $table->timestamps();
                $table->unique(['hotel_code', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('hotel_trader_content_taxes');
    }
};

