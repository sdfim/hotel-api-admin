<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection(config('database.active_connections.mysql_cache'))->table('properties', function (Blueprint $table) {
            if (!Schema::connection(config('database.active_connections.mysql_cache'))->hasColumn('properties', 'search_index')) {
                $table->text('search_index')->after('code')->nullable();
            }
        });

        if (Schema::connection(config('database.active_connections.mysql_cache'))->hasColumn('properties', 'search_index')) {
            $indexExists = DB::connection(config('database.active_connections.mysql_cache'))->select(
                "SHOW INDEX FROM properties WHERE Key_name = 'idx_search_index'"
            );

            if (empty($indexExists)) {
                DB::connection(config('database.active_connections.mysql_cache'))->statement("UPDATE properties SET search_index = CONCAT(name, ' ', code)");
                DB::connection(config('database.active_connections.mysql_cache'))->statement("CREATE FULLTEXT INDEX idx_search_index ON properties (search_index)");
            }
        }
    }

    public function down()
    {
        Schema::connection(config('database.active_connections.mysql_cache'))->table('properties', function (Blueprint $table) {
            if (Schema::connection(config('database.active_connections.mysql_cache'))->hasColumn('properties', 'search_index')) {
                $table->dropIndex(['idx_search_index']);
                $table->dropColumn('search_index');
            }
        });
    }
};
