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
		Schema::create('expedia_content_mains', function (Blueprint $table) {
            // $table->id();
			$table->integer('property_id')->index();
			$table->integer('country_hash')->index();
			$table->float('rating')->index()->default(0);
			$table->string('name')->default('');
			$table->float('latitude')->default(0);
			$table->float('longitude')->default(0);
			$table->json('address');
			$table->json('ratings');
			$table->json('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedia_content_mains');
    }

};
