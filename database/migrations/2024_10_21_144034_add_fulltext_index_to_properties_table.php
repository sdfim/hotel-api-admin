<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the 'properties' table exists
        if (Schema::connection(config('database.active_connections.mysql_cache'))->hasTable('properties')) {

            // Check if the FULLTEXT index does not already exist
            $indexExists = DB::connection(config('database.active_connections.mysql_cache'))
                ->select("SHOW INDEX FROM properties WHERE Key_name = 'properties_name_fulltext'");

            if (empty($indexExists)) {
                // Add the FULLTEXT index if it doesn't exist
                DB::connection(config('database.active_connections.mysql_cache'))
                    ->statement("ALTER TABLE properties ADD FULLTEXT `properties_name_fulltext` (`name`)");
            }
        }
    }

    public function down(): void
    {
        // Check if the 'properties' table exists
        if (Schema::connection(config('database.active_connections.mysql_cache'))->hasTable('properties')) {
            // Remove the FULLTEXT index if it exists
            $indexExists = DB::connection(config('database.active_connections.mysql_cache'))
                ->select("SHOW INDEX FROM properties WHERE Key_name = 'properties_name_fulltext'");

            if (!empty($indexExists)) {
                // Drop the FULLTEXT index if it exists
                DB::connection(config('database.active_connections.mysql_cache'))
                    ->statement("ALTER TABLE properties DROP INDEX `properties_name_fulltext`");
            }
        }
    }
};
