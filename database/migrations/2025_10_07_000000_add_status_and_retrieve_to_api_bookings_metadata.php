<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('api_bookings_metadata', function (Blueprint $table) {
            $table->string('status')->nullable()->after('booking_item_data');
            $table->json('retrieve')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('api_bookings_metadata', function (Blueprint $table) {
            $table->dropColumn(['status', 'retrieve']);
        });
    }
};

