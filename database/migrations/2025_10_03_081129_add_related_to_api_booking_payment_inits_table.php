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
        Schema::table('api_booking_payment_inits', function (Blueprint $table) {
            $table->unsignedBigInteger('related_id')->nullable()->after('provider');
            $table->string('related_type')->nullable()->after('related_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_booking_payment_inits', function (Blueprint $table) {
            $table->dropColumn(['related_id', 'related_type']);
        });
    }
};
