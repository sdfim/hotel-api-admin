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
            $table->string('property')->nullable()->change();
            $table->string('destination')->nullable()->change();
            $table->dateTimeTz('travel_date')->nullable()->change();
            $table->foreignId('supplier_id')->nullable()->change();
            $table->foreignId('channel_id')->nullable()->change();
            $table->dateTimeTz('travel_date_to')->nullable()->after('travel_date');
            $table->string('total_guests_comparison_sign', 1)->after('total_guests')->nullable();
            $table->renameColumn('days', 'days_until_travel');
        });

        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->renameColumn('travel_date', 'travel_date_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->string('property')->nullable(false)->change();
            $table->string('destination')->nullable(false)->change();
            $table->dateTimeTz('travel_date_from')->nullable(false)->change();
            $table->foreignId('supplier_id')->nullable(false)->change();
            $table->foreignId('channel_id')->nullable(false)->change();
            $table->renameColumn('travel_date_from', 'travel_date');
            $table->dropColumn('travel_date_to');
            $table->dropColumn('total_guests_comparison_sign');
            $table->renameColumn('days_until_travel', 'days');
        });
    }
};
