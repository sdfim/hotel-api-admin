<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Temporary maintenance migration to synchronize the database schema with the current application models.
     *
     * This migration is necessary because, during the development phase, the original migration files
     * were modified directly instead of creating separate migration files for incremental changes.
     * This approach was initially chosen to simplify maintenance in the early stages of the project (SupplierRepository).
     *
     * This migration resolves discrepancies between the actual database structure and the
     * expected schema defined by the models, ensuring consistency and stability for future migrations.
     */
    public function up(): void
    {
        Schema::table('pd_images', function (Blueprint $table) {
            if (! Schema::hasColumn('pd_images', 'source')) {
                $table->string('source')->default('own')->after('section_id');
            }
        });
    }
};
