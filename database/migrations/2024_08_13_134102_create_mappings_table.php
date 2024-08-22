<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mappings', function (Blueprint $table) {
            $table->id();
            $table->string('giata_id', 255);
            $table->enum('supplier', ['Expedia', 'HBSI', 'IcePortal']);
            $table->string('supplier_id', 255);
            $table->integer('match_percentage')->default(100); // Nuevo campo agregado
            $table->timestamps();

            $table->unique(['giata_id', 'supplier', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mappings');
    }
};