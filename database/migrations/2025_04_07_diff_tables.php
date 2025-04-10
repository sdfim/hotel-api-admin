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
        Schema::table('pd_hotel_web_finders', function (Blueprint $table) {
            if (Schema::hasColumn('pd_hotel_web_finders', 'finder')) {
                $table->string('finder', 1000)->change();
            }
            if (Schema::hasColumn('pd_hotel_web_finders', 'example')) {
                $table->string('example', 1000)->nullable()->change();
            }
            if (! Schema::hasColumn('pd_hotel_web_finders', 'type')) {
                $table->string('type')->nullable();
            }
        });

        Schema::table('pd_product_fees_and_taxes', function (Blueprint $table) {
            if (Schema::hasColumn('pd_product_fees_and_taxes', 'action_type')) {
                $table->string('action_type')->nullable()->change();
            }
            if (Schema::hasColumn('pd_product_fees_and_taxes', 'collected_by')) {
                $table->string('collected_by')->nullable()->change();
            }
        });

        Schema::table('pd_products', function (Blueprint $table) {
            if (Schema::hasColumn('pd_products', 'website')) {
                $table->string('website', 500)->nullable()->change();
            }
        });

        Schema::table('pd_hotel_rates', function (Blueprint $table) {
            if (Schema::hasColumn('pd_hotel_rates', 'name')) {
                $table->string('name', 5000)->change();
            }
            if (Schema::hasColumn('pd_hotel_rates', 'room_ids')) {
                $table->json('room_ids')->change();
                $table->dropColumn('room_ids');
            }
        });

        Schema::table('pd_images', function (Blueprint $table) {
            if (! Schema::hasColumn('pd_images', 'source')) {
                $table->string('source')->default('own');
            }
        });

        Schema::table('pd_contact_information', function (Blueprint $table) {
            if (Schema::hasColumn('pd_contact_information', 'first_name')) {
                $table->string('first_name', 255)->change();
            }
            if (Schema::hasColumn('pd_contact_information', 'job_title')) {
                $table->string('job_title', 255)->nullable()->change();
            }
        });

        Schema::table('pd_product_informative_services', function (Blueprint $table) {
            if (Schema::hasColumn('pd_product_informative_services', 'cost')) {
                $table->decimal('cost', 15, 2)->change();
            }
            if (Schema::hasColumn('pd_product_informative_services', 'total_net')) {
                $table->decimal('total_net', 15, 2)->nullable()->change();
            }
            if (Schema::hasColumn('pd_product_informative_services', 'name')) {
                $table->string('name', 2000)->change();
            }
        });

        Schema::table('pd_hotel_rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('pd_hotel_rooms', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }
};
