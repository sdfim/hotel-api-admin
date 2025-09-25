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
        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->boolean('email_verified')->default(false)->after('booking_item_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->dropColumn('email_verified');
        });
    }
};
