<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pd_product_informative_services_dynamic_columns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_informative_service_id');
            $table->string('name');
            $table->text('value');
            $table->boolean('show_on_invoice')->default(false);
            $table->boolean('show_on_itinerary')->default(false);
            $table->boolean('show_on_vendor_manifest')->default(false);
            $table->foreign('product_informative_service_id', 'fk_prod_info_service_id')
                ->references('id')
                ->on('pd_product_informative_services')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pd_product_informative_services_dynamic_columns');
    }
};
