<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->string('api_token', 80)->unique()->nullable()->after('id');
            $table->unsignedBigInteger('token_id')->nullable()->change();
            $table->string('access_token')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
};
