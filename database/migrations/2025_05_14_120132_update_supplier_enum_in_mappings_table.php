<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mappings', function (Blueprint $table) {
            $table->enum('supplier', [
                MappingSuppliersEnum::Expedia->value,
                MappingSuppliersEnum::HBSI->value,
                MappingSuppliersEnum::IcePortal->value,
                MappingSuppliersEnum::HILTON->value,
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('mappings', function (Blueprint $table) {
            $table->enum('supplier', [
                MappingSuppliersEnum::Expedia->value,
                MappingSuppliersEnum::HBSI->value,
                MappingSuppliersEnum::IcePortal->value,
            ])->change();
        });
    }
};
