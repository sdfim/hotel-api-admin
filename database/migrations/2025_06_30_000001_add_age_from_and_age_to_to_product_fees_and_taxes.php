<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pd_product_fees_and_taxes', function (Blueprint $table) {
            $table->unsignedInteger('age_from')->nullable()->after('old_name');
            $table->unsignedInteger('age_to')->nullable()->after('age_from');
        });
    }

    public function down()
    {
        Schema::table('pd_product_fees_and_taxes', function (Blueprint $table) {
            $table->dropColumn(['age_from', 'age_to']);
        });
    }
};
