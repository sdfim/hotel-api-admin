<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_booking_item_cache', function (Blueprint $table) {
            $table->uuid('booking_item')->primary();

            $table->uuid('search_id');
            $table->foreign('search_id')
                ->references('search_id')
                ->on('api_search_inspector')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->json('child_items')->nullable();
            $table->string('rate_type')->nullable();
            $table->json('booking_item_data');
            $table->json('booking_pricing_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->string('cache_checkpoint')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_booking_item_cache');
    }
};
