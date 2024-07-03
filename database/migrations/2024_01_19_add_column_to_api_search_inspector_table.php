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
        Schema::table('api_search_inspector', function (Blueprint $table) {
            $table->string('original_path')->after('client_response_path')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_search_inspector', function (Blueprint $table) {
            $table->dropColumn('client_response_path');
        });
    }
};
