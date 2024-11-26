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
            $table->dropForeign(['complete_id']);
            $table->dropColumn('complete_id');
            $table->dropColumn('room_by_query');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->string('complete_id')->nullable();
            $table->string('room_by_query')->nullable();
            $table->foreign('complete_id')
                ->references('booking_item')
                ->on('api_booking_items')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
};
