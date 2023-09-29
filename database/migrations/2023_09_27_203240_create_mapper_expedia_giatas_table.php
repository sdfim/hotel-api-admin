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
        Schema::create('mapper_expedia_giatas', function (Blueprint $table) {
            $table->id();
            $table->integer('expedia_id')->index();
			$table->integer('giata_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapper_expedia_giatas');
    }
};
