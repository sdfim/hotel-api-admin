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
        if (! Schema::connection(config('database.active_connections.mysql_cache'))->hasTable('report_mapper_expedia_giata')) {
            Schema::connection(config('database.active_connections.mysql_cache'))->create('report_mapper_expedia_giata', function (Blueprint $table) {

                $table->id();
                $table->integer('expedia_id')->index();
                $table->integer('giata_id')->nullable();
                $table->string('step');
                $table->string('status');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('database.active_connections.mysql_cache'))->dropIfExists('report_mapper_expedia_giata');
    }
};
