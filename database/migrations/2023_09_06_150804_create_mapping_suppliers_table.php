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
        Schema::create('mapping_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('giata_code');
            $table->string('hotel_name');
            $table->string('address');
            $table->string('rating');
            $table->string('latitude');
            $table->string('longitude');
            $table->foreignId('supplier_id')
                ->constrained(
                    table: 'suppliers',
                    indexName: 'mapping_suppliers__supplier_id'
                );
            $table->integer('code_item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down (): void
    {
        Schema::dropIfExists('mapping_suppliers');
    }
};
