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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
			$table->dateTimeTz('date_offload');
			$table->dateTimeTz('date_travel');
			$table->string('passenger_surname');
			$table->foreignId('contains_id')
				->constrained(
					table: 'contains', 
					indexName: 'contains__contains_id'
				);
			$table->foreignId('channel_id')
				->constrained(
					table: 'channels', 
					indexName: 'channels__channel_id'
				);
			$table->float('total_cost', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
