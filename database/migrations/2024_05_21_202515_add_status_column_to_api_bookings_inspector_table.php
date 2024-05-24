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
        Schema::table('api_booking_inspector', function (Blueprint $table) {
            $table->json('status_describe')->nullable()->after('booking_item');
            $table->string('status')->nullable()->after('booking_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_booking_inspector', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('status_describe');
        });
    }
};
