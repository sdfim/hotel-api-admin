<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pd_hotel_rooms', function (Blueprint $table) {
            $table->unsignedInteger('max_occupancy')->nullable()->after('related_rooms');
        });
    }

    public function down()
    {
        Schema::table('pd_hotel_rooms', function (Blueprint $table) {
            $table->dropColumn('max_occupancy');
        });
    }
};
