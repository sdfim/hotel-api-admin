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

            $table->uuid('booking_item')->after('search_id')->nullable();
            $table->foreign('booking_item')
                ->references('booking_item')
                ->on('api_booking_items')
                ->onUpdate('cascade')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_booking_inspector', function (Blueprint $table) {
            $table->dropColumn('booking_item');
        });
    }
};
