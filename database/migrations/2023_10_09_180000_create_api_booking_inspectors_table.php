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
        Schema::create('api_booking_inspectors', function (Blueprint $table) {
            $table->uuid('id')->primary();

			$table->string('type');
			$table->string('sub_type');

			$table->uuid('search_id');
            $table->foreign('search_id')
                ->references('id')
                ->on('api_search_inspectors')
                ->onUpdate('cascade')
                ->onDelete('cascade');

			$table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::dropIfExists('api_booking_inspectors');
    }
};
