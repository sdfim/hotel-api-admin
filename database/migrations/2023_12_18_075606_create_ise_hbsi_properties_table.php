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
        if (! Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->hasTable('ice_hbsi_properties')) {
            Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->create('ice_hbsi_properties', function (Blueprint $table) {
                $table->integer('code')->index()->unique();
                $table->integer('supplier_id');
                $table->string('name')->default('')->index();
                $table->string('city')->default('')->index();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('addressLine1')->nullable();
                $table->string('phone')->nullable();
                $table->float('latitude', 15, 12)->nullable()->index();
                $table->float('longitude', 15, 12)->nullable()->index();

                $table->json('images')->nullable();
                $table->json('amenities')->nullable();

                $table->dateTime('editDate')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION_2', 'mysql2'))->dropIfExists('ice_hbsi_properties');
    }
};
