<?php

namespace Modules\API\Payment\routes;

use Illuminate\Support\Facades\Route;

class PaymentApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('payment')->group(function () {
            Route::post('create', [\Modules\API\Payment\Controllers\AirwallexProxyController::class, 'createPaymentIntent']);
            Route::get('payment-intent/{id}', [\Modules\API\Payment\Controllers\AirwallexProxyController::class, 'retrievePaymentIntent']);
            Route::get('transaction/{booking_id}', [\Modules\API\Payment\Controllers\AirwallexProxyController::class, 'getTransactionByBookingId']);
            Route::post('confirmation', [\Modules\API\Payment\Controllers\AirwallexProxyController::class, 'confirmationPaymentIntent']);
            // Future Airwallex routes can be added here
        });
    }
}
