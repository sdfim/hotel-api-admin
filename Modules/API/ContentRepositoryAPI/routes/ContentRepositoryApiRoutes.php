<?php

namespace Modules\API\ContentRepositoryAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\HotelContentRepository\API\Controllers\ContactInformationController;
use Modules\HotelContentRepository\API\Controllers\ContentSourceController;
use Modules\HotelContentRepository\API\Controllers\HotelController;
use Modules\HotelContentRepository\API\Controllers\HotelRoomController;
use Modules\HotelContentRepository\API\Controllers\HotelWebFinderController;
use Modules\HotelContentRepository\API\Controllers\HotelWebFinderUnitController;
use Modules\HotelContentRepository\API\Controllers\ImageController;
use Modules\HotelContentRepository\API\Controllers\ImageGalleryController;
use Modules\HotelContentRepository\API\Controllers\ImageSectionController;
use Modules\HotelContentRepository\API\Controllers\KeyMappingController;
use Modules\HotelContentRepository\API\Controllers\KeyMappingOwnerController;
use Modules\HotelContentRepository\API\Controllers\ProductAffiliationController;
use Modules\HotelContentRepository\API\Controllers\ProductAgeRestrictionController;
use Modules\HotelContentRepository\API\Controllers\ProductAttributeController;
use Modules\HotelContentRepository\API\Controllers\ProductController;
use Modules\HotelContentRepository\API\Controllers\ProductDepositInformationController;
use Modules\HotelContentRepository\API\Controllers\ProductDescriptiveContentSectionController;
use Modules\HotelContentRepository\API\Controllers\VendorController;

class ContentRepositoryApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('repo')->group(function () {

            // Group HOTEL
            Route::resource('hotels', HotelController::class);
            Route::post('hotels/{id}/attach-web-finder', [HotelController::class, 'attachWebFinder']);
            Route::post('hotels/{id}/detach-web-finder', [HotelController::class, 'detachWebFinder']);

            Route::resource('hotel-rooms', HotelRoomController::class)->names('api.hotel-rooms');
            Route::post('hotel-rooms/{id}/attach-gallery', [HotelRoomController::class, 'attachGallery']);
            Route::post('hotel-rooms/{id}/detach-gallery', [HotelRoomController::class, 'detachGallery']);

            Route::resource('hotel-web-finders', HotelWebFinderController::class);
            Route::resource('hotel-web-finder-units', HotelWebFinderUnitController::class);

            Route::resource('images', ImageController::class)->names('api.images');
            Route::resource('image-sections', ImageSectionController::class);

            Route::resource('image-galleries', ImageGalleryController::class)->names('api.image-galleries');
            Route::post('image-galleries/{id}/attach-image', [ImageGalleryController::class, 'attachImage']);
            Route::post('image-galleries/{id}/detach-image', [ImageGalleryController::class, 'detachImage']);

            // Group VENDOR
            Route::resource('vendors', VendorController::class);

            // Group PRODUCT
            Route::resource('products', ProductController::class);
            Route::post('products/{id}/attach-gallery', [ProductController::class, 'attachGallery']);
            Route::post('products/{id}/detach-gallery', [ProductController::class, 'detachGallery']);

            Route::resource('product-affiliations', ProductAffiliationController::class);

            Route::resource('product-attributes', ProductAttributeController::class);

            Route::resource('product-descriptive-content-sections', ProductDescriptiveContentSectionController::class)
                ->parameters(['product-descriptive-content-sections' => 'section']);

            Route::resource('contact-information', ContactInformationController::class);

            Route::resource('product-deposit-information', ProductDepositInformationController::class);

            Route::resource('key-mappings', KeyMappingController::class);
            Route::resource('key-mapping-owners', KeyMappingOwnerController::class);

            Route::resource('age-restrictions', ProductAgeRestrictionController::class);

            Route::resource('content-sources', ContentSourceController::class);
        });
    }
}
