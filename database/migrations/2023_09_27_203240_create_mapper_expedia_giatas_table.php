<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up (): void
    {
		if (!Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->hasTable('mapper_expedia_giatas')) {
            Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->create('mapper_expedia_giatas', function (Blueprint $table) {
				$table->id();
				$table->integer('expedia_id');
				$table->integer('giata_id');
				// $table->foreign('giata_id')->references('code')->on(env('SECOND_DB_DATABASE', 'ujv_api').'.giata_properties')->onDelete('cascade');
				// $table->foreign('expedia_id')->references('property_id')->on(env('SECOND_DB_DATABASE', 'ujv_api').'.expedia_contents')->onDelete('cascade');
				// $table->connection(env('DB_CONNECTION_2', 'mysql2'))->foreign('giata_id')->references('code')->on('giata_properties')->onDelete('cascade');
				// $table->connection(env('DB_CONNECTION_2', 'mysql2'))->foreign('expedia_id')->references('property_id')->on('expedia_contents')->onDelete('cascade');
				$table->integer('step');
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down (): void
    {
        Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->dropIfExists('mapper_expedia_giatas');
    }
};
