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
            $table->foreignId('channel_id')
                // TODO: we should remove ->nullable() when we clean up migrations and merge columns with different migrations for one table
                ->nullable()
                ->constrained(
                    table: 'channels',
                    indexName: 'pricing_rules__channel_id'
                );
            // TODO: we should remove ->default(now()) when we clean up migrations and merge columns with different migrations for one table
            $table->dateTimeTz('rule_start_date')->default(now());
            // TODO: we should remove ->default(now()) when we clean up migrations and merge columns with different migrations for one table
            $table->dateTimeTz('rule_expiration_date')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropForeign('pricing_rules__channel_id');
            $table->dropColumn('channel_id');
            $table->dropColumn('rule_start_date');
            $table->dropColumn('rule_expiration_date');
        });
    }
};




