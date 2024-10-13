<?php

namespace Modules\API\HotelContentRepositoryAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\DestinationsController;
use Modules\API\Controllers\RouteApiController;
use Modules\HotelContentRepository\Http\Controllers\HotelAffiliationController;
use Modules\HotelContentRepository\Http\Controllers\HotelController;
use Modules\HotelContentRepository\Http\Controllers\HotelFeeTaxController;
use Modules\HotelContentRepository\Http\Controllers\HotelAttributeController;
use Modules\HotelContentRepository\Http\Controllers\HotelDescriptiveContentController;
use Modules\HotelContentRepository\Http\Controllers\HotelImageController;
use Modules\HotelContentRepository\Http\Controllers\HotelInformativeServiceController;
use Modules\HotelContentRepository\Http\Controllers\HotelPromotionController;
use Modules\HotelContentRepository\Http\Controllers\HotelRoomController;
use Modules\HotelContentRepository\Http\Controllers\ImageGalleryController;
use Modules\HotelContentRepository\Http\Controllers\KeyMappingController;
use Modules\HotelContentRepository\Http\Controllers\TravelAgencyCommissionController;

class HotelContentRepositoryApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('repo')->group(function () {
            Route::resource('hotels', HotelController::class);
            Route::post('hotels/{id}/attach-gallery', [HotelController::class, 'attachGallery']);
            Route::post('hotels/{id}/detach-gallery', [HotelController::class, 'detachGallery']);

            Route::resource('hotel-affiliations', HotelAffiliationController::class);

            Route::resource('hotel-attributes', HotelAttributeController::class);

            Route::resource('hotel-descriptive-contents', HotelDescriptiveContentController::class);

            Route::resource('hotel-fee-taxes', HotelFeeTaxController::class);

            Route::resource('hotel-informative-services', HotelInformativeServiceController::class);

            Route::resource('hotel-promotions', HotelPromotionController::class);
            Route::post('hotel-promotions/{id}/attach-gallery', [HotelPromotionController::class, 'attachGallery']);
            Route::post('hotel-promotions/{id}/detach-gallery', [HotelPromotionController::class, 'detachGallery']);

            Route::resource('hotel-rooms', HotelRoomController::class);
            Route::post('hotel-rooms/{id}/attach-gallery', [HotelRoomController::class, 'attachGallery']);
            Route::post('hotel-rooms/{id}/detach-gallery', [HotelRoomController::class, 'detachGallery']);


            Route::resource('key-mappings', KeyMappingController::class);

            Route::resource('travel-agency-commissions', TravelAgencyCommissionController::class);

            Route::resource('hotel-images', HotelImageController::class);

            Route::resource('image-galleries', ImageGalleryController::class);
            Route::post('image-galleries/{id}/attach-image', [ImageGalleryController::class, 'attachImage']);
            Route::post('image-galleries/{id}/detach-image', [ImageGalleryController::class, 'detachImage']);
        });
    }
}
