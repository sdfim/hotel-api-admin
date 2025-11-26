<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Enums\SupplierNameEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_meal_plan_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');

            // GIATA hotel code (we map per hotel only)
            $table->unsignedBigInteger('giata_id');

            // Raw rate plan code from supplier response (may be null)
            $table->string('rate_plan_code_from_supplier')->nullable();

            // Raw meal plan code from supplier response (may be null)
            $table->string('meal_plan_code_from_supplier')->nullable();

            // Our normalized meal plan label (must match MealPlansEnum::values())
            $table->string('our_meal_plan');

            // Toggle to deactivate mapping without deleting the row
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            // Indices
            $table->index('giata_id', 'pd_meal_plan_mappings_giata_id_index');
            $table->index('our_meal_plan', 'pd_meal_plan_mappings_our_meal_plan_index');

            // Composite index for fast lookup by codes
            $table->index(
                ['giata_id', 'rate_plan_code_from_supplier', 'meal_plan_code_from_supplier', 'is_enabled'],
                'pd_meal_plan_mappings_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_meal_plan_mappings');
    }
};
