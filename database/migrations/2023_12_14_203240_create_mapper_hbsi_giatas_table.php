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
        $connection = env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2');

        if (!Schema::connection($connection)->hasTable('mapper_hbsi_giatas')) {
            Schema::connection($connection)->create('mapper_hbsi_giatas', function (Blueprint $table) {
                $table->integer('hbsi_id')->index();
                $table->integer('giata_id')->index();

                $table->index(['hbsi_id', 'giata_id'], 'idx_hbsi_giatas');
                $table->primary(['hbsi_id', 'giata_id']);

                $table->integer('perc');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->dropIfExists('mapper_hbsi_giatas');
    }
};
