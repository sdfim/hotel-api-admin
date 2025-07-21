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
        Schema::table('pd_hotels', function (Blueprint $table) {
            $table->boolean('holdable')->default(false)->after('hotel_board_basis')->comment('Indicates if the hotel can be held for a certain period before booking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pd_hotels', function (Blueprint $table) {
            $table->dropColumn('holdable');
        });
    }
};
