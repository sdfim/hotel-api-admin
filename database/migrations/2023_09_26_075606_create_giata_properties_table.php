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
        if (!Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->hasTable('giata_properties')) {
            Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->create('giata_properties', function (Blueprint $table) {
                $table->integer('code')->index()->unique;
                $table->timestamp('last_updated');
                $table->string('name')->default('');
                $table->json('chain')->nullable();
                $table->string('city')->default('');
                $table->string('locale')->default('');
                $table->json('address');
                $table->json('phone')->nullable();
                $table->json('position')->nullable();
				$table->float('latitude')->nullable()->index();
				$table->float('longitude')->nullable()->index();
                $table->json('url')->nullable();
                $table->json('cross_references');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->dropIfExists('giata_properties');
    }
};
