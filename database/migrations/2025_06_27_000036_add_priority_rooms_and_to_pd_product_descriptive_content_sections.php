<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pd_product_descriptive_content_sections', function (Blueprint $table) {
            $table->json('priority_rooms')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pd_product_descriptive_content_sections', function (Blueprint $table) {
            $table->dropColumn(['priority_rooms']);
        });
    }
};
