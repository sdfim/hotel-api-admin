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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('price_type_to_apply', 40);
            $table->string('price_value_type_to_apply', 40);
            $table->float('price_value_to_apply');
            $table->string('price_value_fixed_type_to_apply', 40);
            $table->json('rules');
            $table->dateTimeTz('rule_start_date')->default(now());
            $table->dateTimeTz('rule_expiration_date')->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
