<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapping_rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('giata_id');
            $table->string('unified_room_code');
            $table->enum('supplier', [MappingSuppliersEnum::Expedia->value, MappingSuppliersEnum::HBSI->value, MappingSuppliersEnum::IcePortal->value]);
            $table->string('supplier_room_code');
            $table->string('supplier_room_name')->nullable();
            $table->integer('match_percentage')->default(100);
            $table->timestamps();

            $table->unique(
                ['giata_id', 'supplier', 'supplier_room_code', 'unified_room_code'],
                'mapping_rooms_unique_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapping_rooms');
    }
};
