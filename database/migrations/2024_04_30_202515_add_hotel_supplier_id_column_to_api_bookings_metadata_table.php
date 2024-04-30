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
        Schema::table('api_bookings_metadata', function (Blueprint $table) {
            $table->string('hotel_supplier_id')->nullable()->after('supplier_booking_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_bookings_metadata', function (Blueprint $table) {
            $table->dropColumn('hotel_supplier_id');
        });
    }
};
