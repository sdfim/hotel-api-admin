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
        Schema::create('property_weighting', function (Blueprint $table) {
            $table->id();
			$table->integer('property');
            $table->integer('weight');
            $table->foreignId('supplier_id')
				->nullable()
                ->constrained(
                    table: 'suppliers',
                    indexName: 'property_weighting__supplier_id'
                );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down (): void
    {
        Schema::dropIfExists('property_weighting');
    }
};
