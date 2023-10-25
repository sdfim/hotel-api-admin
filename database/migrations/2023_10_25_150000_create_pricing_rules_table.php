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
            $table->string('property');
            $table->string('destination');
            $table->dateTimeTz('travel_date');
            $table->foreignId('supplier_id')
                ->constrained(
                    table: 'suppliers',
                    indexName: 'pricing_rules__supplier_id'
                )
                ->cascadeOnDelete();
            $table->foreignId('channel_id')
                ->constrained(
                    table: 'channels',
                    indexName: 'pricing_rules__channel_id'
                )
                ->cascadeOnDelete();
            $table->integer('days')->nullable();
            $table->integer('nights')->nullable();
            $table->string('rate_code')->nullable();
            $table->string('room_type')->nullable();
            $table->integer('total_guests')->nullable();
            $table->integer('room_guests')->nullable();
            $table->integer('number_rooms')->nullable();
            $table->string('meal_plan')->nullable();
            $table->string('rating')->nullable();
            $table->string('price_type_to_apply', 40);
            $table->string('price_value_type_to_apply', 40);
            $table->float('price_value_to_apply');
            $table->string('price_value_fixed_type_to_apply', 40)->nullable();
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
