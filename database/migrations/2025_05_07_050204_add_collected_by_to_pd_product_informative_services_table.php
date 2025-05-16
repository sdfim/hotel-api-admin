<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pd_product_informative_services', function (Blueprint $table) {
            $table->string('collected_by')->default('Direct')->after('max_night_stay'); // Add the column
        });
    }

    public function down()
    {
        Schema::table('pd_product_informative_services', function (Blueprint $table) {
            $table->dropColumn('collected_by'); // Remove the column
        });
    }
};
