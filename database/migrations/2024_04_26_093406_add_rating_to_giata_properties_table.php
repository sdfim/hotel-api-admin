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
        $connection = env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2');

        Schema::connection($connection)->table('giata_properties', function (Blueprint $table) use ($connection) {
            if (!Schema::connection($connection)->hasColumn('giata_properties', 'rating')) {
                $table->float('rating')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->table('giata_properties', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
