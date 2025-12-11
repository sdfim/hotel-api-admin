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
        Schema::table('airwallex_api_logs', function (Blueprint $table) {
            $table->string('payment_intent_id')->nullable()->change();
            $table->string('method_action_id')->after('payment_intent_id');
        });

        // Set method_action_id equal to payment_intent_id for existing rows
        DB::table('airwallex_api_logs')->update([
            'method_action_id' => DB::raw('payment_intent_id'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('airwallex_api_logs', function (Blueprint $table) {
            $table->string('payment_intent_id')->nullable(false)->change();
            $table->dropColumn('method_action_id');
        });
    }
};
