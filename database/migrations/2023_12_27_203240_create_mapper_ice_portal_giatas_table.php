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

        if (!Schema::connection($connection)->hasTable('mapper_ice_portal_giatas')) {
            Schema::connection($connection)->create('mapper_ice_portal_giatas', function (Blueprint $table) {
                $table->integer('ice_portal_id')->index();
                $table->integer('giata_id')->index();

                $table->index(['ice_portal_id', 'giata_id'], 'idx_ice_portal_giatas');
                $table->primary(['ice_portal_id', 'giata_id']);

                $table->integer('perc');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->dropIfExists('mapper_ice_portal_giatas');
    }
};
