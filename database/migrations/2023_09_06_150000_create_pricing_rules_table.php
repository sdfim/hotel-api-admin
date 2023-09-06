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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->string('property');
			$table->string('destination');
			$table->dateTimeTz('travel_date');
			$table->integer('days');
			$table->integer('nights');
			$table->foreignId('supplier_id')
				->constrained(
					table: 'suppliers', 
					indexName: 'pricing_rules__supplier_id'
				);
			$table->string('rate_code');
			$table->string('room_type');
			$table->integer('total_guests');
			$table->integer('room_guests');
			$table->integer('number_rooms');
			$table->string('meal_plan');
			$table->string('rating');

			$table->foreignId('manipulate_data_id')
				->constrained(
					table: 'manipulate_data', 
					indexName: 'pricing_rules__manipulate_data_id'
				);
			$table->foreignId('manipulate_type_id')
				->constrained(
					table: 'manipulate_types', 
					indexName: 'pricing_rules__manipulate_type_id'
				);
			$table->foreignId('manipulate_item_id')
				->constrained(
					table: 'manipulate_items', 
					indexName: 'pricing_rules__manipulate_item_id'
				);
			$table->dateTimeTz('start_date');
			$table->dateTimeTz('end_date');
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
