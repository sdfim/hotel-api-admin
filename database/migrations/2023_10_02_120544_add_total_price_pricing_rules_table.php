<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->string('price_type_to_apply', 40);
            $table->string('price_value_type_to_apply', 40);
            $table->float('price_value_to_apply', 8, 2);
            $table->string('price_value_fixed_type_to_apply', 40)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn('price_type_to_apply');
            $table->dropColumn('price_value_type_to_apply');
            $table->dropColumn('price_value_to_apply');
            $table->dropColumn('price_value_fixed_type_to_apply');
        });
    }
};
