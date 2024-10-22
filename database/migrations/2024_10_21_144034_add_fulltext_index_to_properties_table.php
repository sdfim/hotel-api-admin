<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $cacheDB;

    public function __construct()
    {
        $this->cacheDB = config('database.connections.mysql_cache.database');
    }

    public function up(): void
    {
        // Check if the 'properties' table exists
        if (Schema::hasTable('properties')) {

            // Check if the FULLTEXT index does not already exist
            $indexExists = DB::select("SHOW INDEX FROM $this->cacheDB.properties WHERE Key_name = 'properties_name_fulltext'");

            if (empty($indexExists)) {
                // Add the FULLTEXT index if it doesn't exist
                DB::statement("ALTER TABLE `$this->cacheDB.properties` ADD FULLTEXT `properties_name_fulltext` (`name`)");
            }
        }
    }

    public function down(): void
    {
        // Check if the 'properties' table exists
        if (Schema::hasTable('properties')) {
            // Remove the FULLTEXT index if it exists
            $indexExists = DB::select("SHOW INDEX FROM $this->cacheDB.properties WHERE Key_name = 'properties_name_fulltext'");

            if (!empty($indexExists)) {
                // Drop the FULLTEXT index if it exists
                DB::statement("ALTER TABLE `$this->cacheDB.properties` DROP INDEX `properties_name_fulltext`");
            }
        }
    }
};
