<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_attribute_category_pivot', function (Blueprint $table) {
            $table->unsignedBigInteger('config_attribute_id');
            $table->unsignedBigInteger('config_attribute_category_id');

            // Foreign key constraints with shorter names
            $table->foreign('config_attribute_id', 'fk_config_attr_id')
                ->references('id')
                ->on('config_attributes')
                ->onDelete('cascade');

            $table->foreign('config_attribute_category_id', 'fk_config_attr_cat_id')
                ->references('id')
                ->on('config_attribute_categories')
                ->onDelete('cascade');

            // Unique constraint to prevent duplicate entries
            $table->unique(['config_attribute_id', 'config_attribute_category_id'], 'config_attr_cat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_attribute_category_pivot');
    }
};
