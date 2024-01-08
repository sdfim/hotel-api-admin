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
        if (!Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->hasTable('report_mapper_expedia_giata')) {
            Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->create('report_mapper_expedia_giata', function (Blueprint $table) {

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
        Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->dropIfExists('report_mapper_expedia_giata');
    }
};
