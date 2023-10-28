<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = env('DB_CONNECTION_2', 'mysql2');

        if (!Schema::connection($connection)->hasTable('mapper_expedia_giatas')) {
            Schema::connection($connection)->create('mapper_expedia_giatas', function (Blueprint $table) use ($connection) 
			{
				$table->id();

                $table->integer('expedia_id');
                $table->integer('giata_id');

                $table->integer('step');
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->dropIfExists('mapper_expedia_giatas');
    }
};
