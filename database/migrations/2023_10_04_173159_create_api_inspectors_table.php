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
        Schema::create('api_inspectors', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('type');

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

            $table->string('response_path')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_inspectors');
    }
};
