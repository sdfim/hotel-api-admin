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
        Schema::create('general_configurations', function (Blueprint $table) {
            $table->id();
            $table->integer('time_supplier_requests');
            $table->integer('time_reservations_kept');
            $table->string('currently_suppliers');
            // $table->foreignId('channel_id')
            // 	->constrained(
            // 		table: 'channels',
            // 		indexName: 'general_configurations__channel_id'
            // 	);
            $table->integer('time_inspector_retained');
            $table->dateTimeTz('star_ratings');
            $table->dateTimeTz('stop_bookings');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_configurations');
    }
};
