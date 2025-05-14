<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->string('cache_checkpoint')->nullable();
            $table->dropColumn(['hotel_id', 'room_id']);
        });
    }

    public function down()
    {
        Schema::table('api_booking_items', function (Blueprint $table) {
            $table->dropColumn('cache_checkpoint');
            $table->string('hotel_id')->nullable();
            $table->string('room_id')->nullable();
        });
    }
};
