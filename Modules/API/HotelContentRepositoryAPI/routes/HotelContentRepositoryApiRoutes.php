<?php

namespace Modules\API\HotelContentRepositoryAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\DestinationsController;
use Modules\API\Controllers\RouteApiController;
use Modules\HotelContentRepository\API\Controllers\ContentSourceController;
use Modules\HotelContentRepository\API\Controllers\ProductAffiliationController;
use Modules\HotelContentRepository\API\Controllers\ProductAgeRestrictionController;
use Modules\HotelContentRepository\API\Controllers\ProductContactInformationController;
use Modules\HotelContentRepository\API\Controllers\HotelController;
use Modules\HotelContentRepository\API\Controllers\ProductDepositInformationController;
use Modules\HotelContentRepository\API\Controllers\ProductDescriptiveContentSectionController;
use Modules\HotelContentRepository\API\Controllers\ProductFeeTaxController;
use Modules\HotelContentRepository\API\Controllers\ProductAttributeController;
use Modules\HotelContentRepository\API\Controllers\ProductDescriptiveContentController;
use Modules\HotelContentRepository\API\Controllers\ImageController;
use Modules\HotelContentRepository\API\Controllers\ImageSectionController;
use Modules\HotelContentRepository\API\Controllers\ProductInformativeServiceController;
use Modules\HotelContentRepository\API\Controllers\ProductPromotionController;
use Modules\HotelContentRepository\API\Controllers\HotelRoomController;
use Modules\HotelContentRepository\API\Controllers\HotelWebFinderController;
use Modules\HotelContentRepository\API\Controllers\HotelWebFinderUnitController;
use Modules\HotelContentRepository\API\Controllers\ImageGalleryController;
use Modules\HotelContentRepository\API\Controllers\KeyMappingController;
use Modules\HotelContentRepository\API\Controllers\KeyMappingOwnerController;
use Modules\HotelContentRepository\API\Controllers\TravelAgencyCommissionController;

class HotelContentRepositoryApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('repo')->group(function () {

//            Route::prefix('hotel')->group(function () {
//                Route::resource('hotels', HotelController::class);
//                Route::post('hotels/{id}/attach-gallery', [HotelController::class, 'attachGallery']);
//                Route::post('hotels/{id}/detach-gallery', [HotelController::class, 'detachGallery']);
//                Route::post('hotels/{id}/attach-web-finder', [HotelController::class, 'attachWebFinder']);
//                Route::post('hotels/{id}/detach-web-finder', [HotelController::class, 'detachWebFinder']);
//
//                Route::resource('rooms', HotelRoomController::class);
//                Route::post('rooms/{id}/attach-gallery', [HotelRoomController::class, 'attachGallery']);
//                Route::post('rooms/{id}/detach-gallery', [HotelRoomController::class, 'detachGallery']);
//
//                Route::resource('web-finders', HotelWebFinderController::class);
//                Route::resource('web-finder-units', HotelWebFinderUnitController::class);
//            });

            // Group HOTEL
            Route::resource('hotels', HotelController::class);
            Route::post('hotels/{id}/attach-gallery', [HotelController::class, 'attachGallery']);
            Route::post('hotels/{id}/detach-gallery', [HotelController::class, 'detachGallery']);
            Route::post('hotels/{id}/attach-web-finder', [HotelController::class, 'attachWebFinder']);
            Route::post('hotels/{id}/detach-web-finder', [HotelController::class, 'detachWebFinder']);

            Route::resource('hotel-rooms', HotelRoomController::class);
            Route::post('hotel-rooms/{id}/attach-gallery', [HotelRoomController::class, 'attachGallery']);
            Route::post('hotel-rooms/{id}/detach-gallery', [HotelRoomController::class, 'detachGallery']);

            Route::resource('hotel-web-finders', HotelWebFinderController::class);
            Route::resource('hotel-web-finder-units', HotelWebFinderUnitController::class);

            Route::resource('images', ImageController::class);
            Route::resource('image-sections', ImageSectionController::class);

            Route::resource('image-galleries', ImageGalleryController::class);
            Route::post('image-galleries/{id}/attach-image', [ImageGalleryController::class, 'attachImage']);
            Route::post('image-galleries/{id}/detach-image', [ImageGalleryController::class, 'detachImage']);

            // Group PRODUCT
            Route::resource('product-promotions', ProductPromotionController::class);
            Route::post('product-promotions/{id}/attach-gallery', [ProductPromotionController::class, 'attachGallery']);
            Route::post('product-promotions/{id}/detach-gallery', [ProductPromotionController::class, 'detachGallery']);

            Route::resource('product-affiliations', ProductAffiliationController::class);

            Route::resource('product-attributes', ProductAttributeController::class);

            Route::resource('product-descriptive-content-sections', ProductDescriptiveContentSectionController::class)
                ->parameters(['product-descriptive-content-sections' => 'section']);
            Route::resource('product-descriptive-contents', ProductDescriptiveContentController::class);

            Route::resource('product-fee-taxes', ProductFeeTaxController::class);

            Route::resource('product-informative-services', ProductInformativeServiceController::class);

            Route::resource('product-contact-information', ProductContactInformationController::class);

            Route::resource('product-deposit-information', ProductDepositInformationController::class);

            Route::resource('key-mappings', KeyMappingController::class);
            Route::resource('key-mapping-owners', KeyMappingOwnerController::class);

            Route::resource('age-restrictions', ProductAgeRestrictionController::class);

            Route::resource('travel-agency-commissions', TravelAgencyCommissionController::class);

            Route::resource('content-sources', ContentSourceController::class);
        });
    }
}
