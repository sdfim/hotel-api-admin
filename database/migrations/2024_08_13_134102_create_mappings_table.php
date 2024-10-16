<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mappings', function (Blueprint $table) {
            $table->id();
            $table->integer('giata_id');
            $table->enum('supplier', [MappingSuppliersEnum::Expedia->value, MappingSuppliersEnum::HBSI->value, MappingSuppliersEnum::IcePortal->value]);
            $table->string('supplier_id', 255);
            $table->integer('match_percentage')->default(100); // Nuevo campo agregado
            $table->timestamps();

            $table->unique(['giata_id', 'supplier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mappings');
    }
};