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
        Schema::dropIfExists('api_booking_inspectors');

        Schema::dropIfExists('api_search_inspectors');

        Schema::create('api_search_inspector', function (Blueprint $table) {
            
			$table->uuid('search_id')->index()->primary();

            $table->string('type');

            $table->string('search_type')->nullable();

            $table->string('suppliers');

            $table->unsignedBigInteger('token_id');
            $table->foreign('token_id')
                ->references('id')
                ->on('personal_access_tokens')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->json('request');

            $table->string('response_path');

            $table->string('client_response_path');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_search_inspector');
    }
};
