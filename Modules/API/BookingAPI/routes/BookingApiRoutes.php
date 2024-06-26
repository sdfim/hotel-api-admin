<?php

namespace Modules\API\BookingAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Controllers\RouteBookingApiController;

class BookingApiRoutes
{
    public static function routes(): void
    {
        Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'booking'], function () {
            Route::post('/add-item', [RouteBookingApiController::class, 'handle'])->name('addItem');
            Route::delete('/remove-item', [RouteBookingApiController::class, 'handle'])->name('removeItem');
            Route::get('/retrieve-items', [BookApiHandler::class, 'retrieveItems'])->name('retrieveItems');
            Route::post('/add-passengers', [BookApiHandler::class, 'addPassengers'])->name('addPassengers');

            Route::post('/book', [BookApiHandler::class, 'book'])->name('book');
            Route::get('/list-bookings', [BookApiHandler::class, 'listBookings'])->name('listBookings');
            Route::put('/change-booking', [BookApiHandler::class, 'changeBooking'])->name('changeBooking');
            Route::get('/retrieve-booking', [BookApiHandler::class, 'retrieveBooking'])->name('retrieveBooking');
            Route::delete('/cancel-booking', [BookApiHandler::class, 'cancelBooking'])->name('cancelBooking');
        });
    }
}
