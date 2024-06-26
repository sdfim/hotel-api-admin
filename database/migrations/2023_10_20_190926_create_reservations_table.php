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
            $table->dateTimeTz('date_offload')->nullable();
            $table->dateTimeTz('date_travel');
            $table->string('passenger_surname');
            $table->json('reservation_contains');
            $table->foreignId('channel_id')
                ->constrained(
                    table: 'channels',
                    indexName: 'channels__channel_id'
                );
            $table->float('total_cost', 8, 2);
            $table->dateTimeTz('canceled_at')->nullable();
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
