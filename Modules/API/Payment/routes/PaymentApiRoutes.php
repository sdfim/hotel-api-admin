<?php

namespace Modules\API\Payment\routes;

use Illuminate\Support\Facades\Route;

class PaymentApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('payment')->group(function () {
            Route::post('create', [\Modules\API\Payment\Controllers\PaymentController::class, 'createPaymentIntent']);
            Route::post('confirmation', [\Modules\API\Payment\Controllers\PaymentController::class, 'confirmationPaymentIntent']);

            Route::get('payment-intent/{id}', [\Modules\API\Payment\Controllers\PaymentController::class, 'retrievePaymentIntent']);
            Route::get('transaction/{booking_id}', [\Modules\API\Payment\Controllers\PaymentController::class, 'getTransactionByBookingId']);
        });
    }
}
