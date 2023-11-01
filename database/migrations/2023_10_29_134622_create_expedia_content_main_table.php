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
        if (!Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->hasTable('expedia_content_main')) {
            Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->create('expedia_content_main', function (Blueprint $table) {
                $table->integer('property_id')->index()->unique();
                $table->float('rating')->index()->default(0);
                $table->string('name');
                $table->string('city')->index();
                $table->string('latitude');
                $table->string('longitude');
                $table->json('address');
                $table->json('ratings');
                $table->json('location');
                $table->string('phone');
                $table->string('total_occupancy');
				$table->boolean('is_active')->default(true);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->dropIfExists('expedia_content_main');
    }
};
