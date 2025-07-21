<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pd_product_affiliation_amenities', function (Blueprint $table) {
            $table->json('priority_rooms')->nullable()->after('max_night_stay');
            $table->json('drivers')->nullable()->after('priority_rooms');
        });
    }

    public function down(): void
    {
        Schema::table('pd_product_affiliation_amenities', function (Blueprint $table) {
            $table->dropColumn(['priority_rooms', 'drivers']);
        });
    }
};
