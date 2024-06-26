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
        $connection = env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache');

        if (! Schema::connection($connection)->hasTable('mapper_expedia_giatas')) {
            Schema::connection($connection)->create('mapper_expedia_giatas', function (Blueprint $table) {
                $table->integer('expedia_id')->index();
                $table->integer('giata_id')->index();

                $table->index(['expedia_id', 'giata_id'], 'idx_expedia_giatas');
                $table->primary(['expedia_id', 'giata_id']);

                $table->integer('step');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->dropIfExists('mapper_expedia_giatas');
    }
};
