<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('airwallex_api_logs', function (Blueprint $table) {
            $table->uuid('booking_id')->nullable()->after('status_code');
        });
    }

    public function down()
    {
        Schema::table('airwallex_api_logs', function (Blueprint $table) {
            $table->dropColumn('booking_id');
        });
    }
};
