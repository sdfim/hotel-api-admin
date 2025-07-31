<?php

namespace Modules\API\PushContent\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\PushContent\Controllers\HotelTraderPushController;
use Modules\API\PushContent\Http\Middleware\HotelTraderVerifyBasicToken;

class HotelTraderPushContent
{
    public static function routes(): void
    {
        Route::prefix('push-hoteltrader')->middleware(HotelTraderVerifyBasicToken::class)->group(function () {
            // Hotel Trader Push Content Routes
            Route::post('/hotel-audit', [HotelTraderPushController::class, 'auditHotel']);
            Route::post('/hotels', [HotelTraderPushController::class, 'storeHotels']);
            Route::put('/hotel/{code}', [HotelTraderPushController::class, 'updateHotel']);

            // Room Types
            Route::post('/roomtype-audit', [HotelTraderPushController::class, 'auditRoomtype']);
            Route::post('/roomtypes', [HotelTraderPushController::class, 'storeRoomTypes']);
            Route::put('/roomtype/{code}', [HotelTraderPushController::class, 'updateRoomType']);

            // Rates
            Route::post('/rateplan-audit', [HotelTraderPushController::class, 'auditRateplan']);
            Route::post('/rateplans', [HotelTraderPushController::class, 'storeRatePlans']);
            Route::put('/rateplan/{code}', [HotelTraderPushController::class, 'updateRatePlan']);

            // cancellation-policies
            Route::post('/cancellation-policy-audit', [HotelTraderPushController::class, 'auditCancellationPolicy']);
            Route::post('/cancellation-policies', [HotelTraderPushController::class, 'storeCancellationPolicies']);
            Route::put('/cancellation-policy/{code}', [HotelTraderPushController::class, 'updateCancellationPolicie']);

            // taxes
            Route::post('/tax-audit', [HotelTraderPushController::class, 'auditTax']);
            Route::post('/taxes', [HotelTraderPushController::class, 'storeTaxes']);
            Route::put('/tax/{code}', [HotelTraderPushController::class, 'updateTax']);

            // products
            Route::post('/products-audit', [HotelTraderPushController::class, 'auditProducts']);
            Route::post('/products', [HotelTraderPushController::class, 'storeProducts']);
        });
    }
}
