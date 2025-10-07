<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->boolean('email_verified')->nullable()->change();
        });

        DB::table('api_booking_items')
            ->where('email_verified', 0)
            ->update(['email_verified' => null]);
    }

    public function down(): void
    {
        DB::table('api_booking_items')
            ->whereNull('email_verified')
            ->update(['email_verified' => 0]);

        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->boolean('email_verified')->nullable(false)->default(false)->change();
        });
    }
};
