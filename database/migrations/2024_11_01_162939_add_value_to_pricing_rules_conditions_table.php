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
        Schema::table('pricing_rules_conditions', function (Blueprint $table) {
            $table->json('value')->after('compare')->nullable();
            $table->string('value_from')->nullable()->change();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules_conditions', function (Blueprint $table) {
            $table->dropColumn('value');
            $table->string('value_from')->nullable(false)->change();
        });
    }
};
