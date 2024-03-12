<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->hasTable('expedia_content_slave')) {
            Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->create('expedia_content_slave', function (Blueprint $table) {
                $table->integer('expedia_property_id')->index()->unique();
                $table->string('fax');
                $table->string('tax_id');
                $table->json('category');
                $table->json('business_model');
                $table->string('rank');
                $table->json('checkin');
                $table->json('checkout');
                $table->json('fees');
                $table->json('policies');
                $table->json('attributes');
                $table->json('amenities');
                $table->json('images');
                $table->json('onsite_payments');
                $table->json('rooms');
                $table->json('rooms_occupancy');
                $table->json('rates');
                $table->json('dates');
                $table->json('descriptions');
                $table->json('themes');
                $table->json('chain');
                $table->json('brand');
                $table->json('statistics');
                $table->boolean('multi_unit')->default(false);
                $table->boolean('payment_registration_recommended')->default(false);
                $table->json('vacation_rental_details');
                $table->json('airports');
                $table->json('spoken_languages');
                $table->string('supply_source')->default('expedia');
                $table->json('all_inclusive');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('SUPPLIER_CONTENT_DB_CONNECTION', 'mysql2'))->dropIfExists('expedia_content_slave');
    }
};
