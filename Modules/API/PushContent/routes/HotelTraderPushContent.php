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
            Route::post('/hotels', [HotelTraderPushController::class, 'storeHotels']);
            Route::put('/hotel/{code}', [HotelTraderPushController::class, 'updateHotel']);

            // Room Types
            Route::post('/roomtypes', [HotelTraderPushController::class, 'storeRoomTypes']);
            Route::put('/roomtype/{code}', [HotelTraderPushController::class, 'updateRoomType']);

            // Rates
            Route::post('/rateplans', [HotelTraderPushController::class, 'storeRates']);
            Route::put('/rateplan/{code}', [HotelTraderPushController::class, 'updateRate']);

            // cancellation-policies
            Route::post('/cancellation-policies', [HotelTraderPushController::class, 'storeCancellationPolicies']);
            Route::put('/cancellation-policy/{code}', [HotelTraderPushController::class, 'updateCancellationPolicie']);

            // taxes
            Route::post('/taxes', [HotelTraderPushController::class, 'storeTaxes']);
            Route::put('/tax/{code}', [HotelTraderPushController::class, 'updateTax']);

        });
    }
}
