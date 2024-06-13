<?php

namespace Modules\API\BookingAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Controllers\RouteBookingApiController;

class BookingApiRoutes
{
    /**
     * @return void
     */
    public static function routes(): void
    {
        Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'booking'], function () {
            Route::post('/add-item', [RouteBookingApiController::class, 'handle'])->name('addItem');
            Route::delete('/remove-item', [RouteBookingApiController::class, 'handle'])->name('removeItem');
            Route::get('/retrieve-items', [BookApiHandler::class, 'retrieveItems'])->name('retrieveItems');
            Route::post('/add-passengers', [BookApiHandler::class, 'addPassengers'])->name('addPassengers');

            Route::post('/book', [BookApiHandler::class, 'book'])->name('book');
            Route::get('/list-bookings', [BookApiHandler::class, 'listBookings'])->name('listBookings');
            // TODO: need to delete this route after refactoring (we ned use only one route for change booking /change/soft-change)
            Route::put('/change-booking', [BookApiHandler::class, 'changeBooking'])->name('changeBooking');

            Route::get('/retrieve-booking', [BookApiHandler::class, 'retrieveBooking'])->name('retrieveBooking');
            Route::delete('/cancel-booking', [BookApiHandler::class, 'cancelBooking'])->name('cancelBooking');

            Route::get('/options-change-booking', [BookApiHandler::class, 'optionsChangeBooking'])->name('optionsChangeBooking');


            Route::get('/change/additional_rates', [BookApiHandler::class, 'additionalRates'])->name('additionalRates');
            // TODO: need to change name method to changeSoftBooking
            Route::put('/change/soft-change', [BookApiHandler::class, 'changeBooking'])->name('changeBooking');
            Route::put('/change/hard-change', [BookApiHandler::class, 'changeHardBooking'])->name('changeHardBooking');
        });
    }
}
