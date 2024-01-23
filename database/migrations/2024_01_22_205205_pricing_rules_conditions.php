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
        Schema::create('pricing_rules_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('field');
            $table->string('compare', 10);
            $table->string('value_from');
            $table->string('value_to')->nullable();
            $table->foreignId('pricing_rule_id')
                ->constrained(
                    table: 'pricing_rules',
                    indexName: 'pricing_rules__rule_id'
                )
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules_conditions');
    }
};
