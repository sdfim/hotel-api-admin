<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_configurations', function (Blueprint $table) {
            $table->string('content_supplier')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('general_configurations', function (Blueprint $table) {
            $table->string('content_supplier')->nullable(false)->change();
        });
    }
};
