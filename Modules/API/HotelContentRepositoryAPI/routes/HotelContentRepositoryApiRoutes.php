<?php

namespace Modules\API\HotelContentRepositoryAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\DestinationsController;
use Modules\API\Controllers\RouteApiController;
use Modules\HotelContentRepository\API\Controllers\ContentSourceController;
use Modules\HotelContentRepository\API\Controllers\HotelAffiliationController;
use Modules\HotelContentRepository\API\Controllers\HotelAgeRestrictionController;
use Modules\HotelContentRepository\API\Controllers\HotelAgeRestrictionTypeController;
use Modules\HotelContentRepository\API\Controllers\HotelContactInformationController;
use Modules\HotelContentRepository\API\Controllers\HotelController;
use Modules\HotelContentRepository\API\Controllers\HotelDepositInformationController;
use Modules\HotelContentRepository\API\Controllers\HotelDescriptiveContentSectionController;
use Modules\HotelContentRepository\API\Controllers\HotelFeeTaxController;
use Modules\HotelContentRepository\API\Controllers\HotelAttributeController;
use Modules\HotelContentRepository\API\Controllers\HotelDescriptiveContentController;
use Modules\HotelContentRepository\API\Controllers\HotelImageController;
use Modules\HotelContentRepository\API\Controllers\HotelImageSectionController;
use Modules\HotelContentRepository\API\Controllers\HotelInformativeServiceController;
use Modules\HotelContentRepository\API\Controllers\HotelPromotionController;
use Modules\HotelContentRepository\API\Controllers\HotelRoomController;
use Modules\HotelContentRepository\API\Controllers\HotelWebFinderController;
use Modules\HotelContentRepository\API\Controllers\HotelWebFinderUnitController;
use Modules\HotelContentRepository\API\Controllers\ImageGalleryController;
use Modules\HotelContentRepository\API\Controllers\KeyMappingController;
use Modules\HotelContentRepository\API\Controllers\KeyMappingOwnerController;
use Modules\HotelContentRepository\API\Controllers\TravelAgencyCommissionController;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;

class HotelContentRepositoryApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('repo')->group(function () {
            Route::resource('hotels', HotelController::class);
            Route::post('hotels/{id}/attach-gallery', [HotelController::class, 'attachGallery']);
            Route::post('hotels/{id}/detach-gallery', [HotelController::class, 'detachGallery']);
            Route::post('hotels/{id}/attach-web-finder', [HotelController::class, 'attachWebFinder']);
            Route::post('hotels/{id}/detach-web-finder', [HotelController::class, 'detachWebFinder']);

            Route::resource('hotel-images', HotelImageController::class);
            Route::resource('hotel-image-sections', HotelImageSectionController::class);

            Route::resource('image-galleries', ImageGalleryController::class);
            Route::post('image-galleries/{id}/attach-image', [ImageGalleryController::class, 'attachImage']);
            Route::post('image-galleries/{id}/detach-image', [ImageGalleryController::class, 'detachImage']);

            Route::resource('hotel-promotions', HotelPromotionController::class);
            Route::post('hotel-promotions/{id}/attach-gallery', [HotelPromotionController::class, 'attachGallery']);
            Route::post('hotel-promotions/{id}/detach-gallery', [HotelPromotionController::class, 'detachGallery']);

            Route::resource('hotel-rooms', HotelRoomController::class);
            Route::post('hotel-rooms/{id}/attach-gallery', [HotelRoomController::class, 'attachGallery']);
            Route::post('hotel-rooms/{id}/detach-gallery', [HotelRoomController::class, 'detachGallery']);

            Route::resource('hotel-affiliations', HotelAffiliationController::class);

            Route::resource('hotel-attributes', HotelAttributeController::class);

            Route::resource('hotel-descriptive-content-sections', HotelDescriptiveContentSectionController::class)
                ->parameters(['hotel-descriptive-content-sections' => 'section']);
            Route::resource('hotel-descriptive-contents', HotelDescriptiveContentController::class);

            Route::resource('hotel-fee-taxes', HotelFeeTaxController::class);

            Route::resource('hotel-informative-services', HotelInformativeServiceController::class);

            Route::resource('hotel-contact-information', HotelContactInformationController::class);

            Route::resource('hotel-deposit-information', HotelDepositInformationController::class);

            Route::resource('hotel-web-finders', HotelWebFinderController::class);
            Route::resource('hotel-web-finder-units', HotelWebFinderUnitController::class);

            Route::resource('key-mappings', KeyMappingController::class);
            Route::resource('key-mapping-owners', KeyMappingOwnerController::class);

            Route::resource('age-restrictions', HotelAgeRestrictionController::class);
            Route::resource('age-restriction-types', HotelAgeRestrictionTypeController::class);

            Route::resource('travel-agency-commissions', TravelAgencyCommissionController::class);

            Route::resource('content-sources', ContentSourceController::class);
        });
    }
}
