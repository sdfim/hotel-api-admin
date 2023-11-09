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
        Schema::table('api_booking_items', function (Blueprint $table) {

			$table->json('booking_pricing_data')->after('booking_item_data')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->dropColumn('booking_pricing_data');
        });
    }
};
