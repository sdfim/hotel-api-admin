<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pd_product_affiliation_amenities', function (Blueprint $table) {
            $table->text('description')->nullable()->after('drivers');
        });
    }

    public function down()
    {
        Schema::table('pd_product_affiliation_amenities', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
