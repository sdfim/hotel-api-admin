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
        Schema::create('api_exception_reports', function (Blueprint $table) {
            $table->id();

			$table->uuid('report_id');

			$table->string('level');

			$table->unsignedBigInteger('supplier_id');
			$table->foreign('supplier_id')
				->references('id')
				->on('suppliers')
				->onUpdate('cascade')
                ->onDelete('cascade');

			$table->string('action');
			$table->string('description');

			$table->string('response_path')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_exception_reports');
    }
};
