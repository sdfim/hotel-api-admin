<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::connection(config('database.active_connections.mysql_cache'))->hasTable('ice_portal_property_assets')) {
            Schema::connection(config('database.active_connections.mysql_cache'))->create('ice_portal_property_assets', function (Blueprint $table) {
                $table->id('listingID');
                $table->string('type');
                $table->string('name');
                $table->unsignedBigInteger('supplierId');
                $table->string('supplierChainCode');
                $table->string('supplierMappedID');
                $table->timestamp('createdOn')->nullable();
                $table->timestamp('propertyLastModified')->nullable();
                $table->timestamp('contentLastModified')->nullable();
                $table->timestamp('makeLiveDate')->nullable();
                $table->string('makeLiveBy')->nullable();
                $table->string('editDate')->nullable();
                $table->string('editBy')->nullable();
                $table->string('addressLine1');
                $table->string('city');
                $table->string('country');
                $table->string('postalCode');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->string('listingClassName')->nullable();
                $table->string('regionCode')->nullable();
                $table->string('phone')->nullable();
                $table->string('publicationStatus')->nullable();
                $table->string('listingURL')->nullable();
                $table->string('bookingURL')->nullable();
                $table->timestamp('publishedDate')->nullable();
                $table->json('roomTypes')->nullable();
                $table->json('meetingRooms')->nullable();
                $table->integer('iceListingQuantityScore')->nullable();
                $table->integer('iceListingSizeScore')->nullable();
                $table->integer('iceListingCategoryScore');
                $table->integer('iceListingRoomScore')->nullable();
                $table->integer('iceListingScore')->nullable();
                $table->integer('bookingListingScore')->nullable();
                $table->json('assets')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::connection(config('database.active_connections.mysql_cache'))->dropIfExists('ice_portal_property_assets');

    }
};
