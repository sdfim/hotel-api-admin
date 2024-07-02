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
        Schema::table('general_configurations', function (Blueprint $table) {
            $table->string('content_supplier')->after('stop_bookings')->default('Expedia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_configurations', function (Blueprint $table) {
            $table->dropColumn('content_supplier');
        });
    }
};
