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
        if (! Schema::connection(config('database.active_connections.mysql_cache'))->hasTable('oracle_contents')) {
            Schema::connection(config('database.active_connections.mysql_cache'))->create('oracle_contents', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->index()->comment('Hotel or other content key code');
                $table->json('rooms')->nullable()->comment('Raw JSON data for physical rooms');
                $table->json('room_classes')->nullable()->comment('Raw JSON data for room classes');
                $table->json('room_types')->nullable()->comment('Raw JSON data for room types');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('database.active_connections.mysql_cache'))->dropIfExists('oracle_contents');
    }
};
