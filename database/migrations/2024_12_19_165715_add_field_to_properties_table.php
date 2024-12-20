<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->table('properties', function (Blueprint $table) {
            if (!Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasColumn('properties', 'search_index')) {
                $table->text('search_index')->after('code')->nullable();
            }
        });

        if (!Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasColumn('properties', 'search_index')) {
            DB::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->statement("UPDATE properties SET search_index = CONCAT(name, ' ', code)");
            DB::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->statement("CREATE FULLTEXT INDEX idx_search_index ON properties (search_index)");
        }
    }

    public function down()
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->table('properties', function (Blueprint $table) {
            if (Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql_cache'))->hasColumn('properties', 'search_index')) {
                $table->dropIndex(['idx_search_index']);
                $table->dropColumn('search_index');
            }
        });
    }
};
