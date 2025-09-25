<?php

namespace Modules\API\BookingAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Controllers\RouteBookingApiController;

class BookingApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('booking')->group(function () {
            // Section Basket
            Route::post('/add-item', [RouteBookingApiController::class, 'handle'])->name('addItem');
            Route::delete('/remove-item', [RouteBookingApiController::class, 'handle'])->name('removeItem');
            Route::get('/retrieve-items', [BookApiHandler::class, 'retrieveItems'])->name('retrieveItems');
            Route::post('/add-passengers', [BookApiHandler::class, 'addPassengers'])->name('addPassengers');
            // Section Booking
            Route::post('/book', [BookApiHandler::class, 'book'])->name('book');
            Route::get('/list-bookings', [BookApiHandler::class, 'listBookings'])->name('listBookings');
            Route::get('/retrieve-booking', [BookApiHandler::class, 'retrieveBooking'])->name('retrieveBooking');
            Route::delete('/cancel-booking', [BookApiHandler::class, 'cancelBooking'])->name('cancelBooking');
            // Section Change Booking
            Route::get('/change/available-endpoints', [BookApiHandler::class, 'availableEndpoints'])->name('availableEndpoints');
            Route::put('/change/soft-change', [BookApiHandler::class, 'changeSoftBooking'])->name('changeSoftBooking');
            Route::post('/change/availability', [BookApiHandler::class, 'availabilityChange'])->name('availabilityChange');
            Route::get('/change/price-check', [BookApiHandler::class, 'priceCheck'])->name('priceCheck');
            Route::put('/change/hard-change', [BookApiHandler::class, 'changeHardBooking'])->name('changeHardBooking');
            // Section Quote Management
            Route::get('/list-quote', [BookApiHandler::class, 'listQuote'])->name('listQuote');
            Route::get('/retrieve-quote', [BookApiHandler::class, 'retrieveQuote'])->name('retrieveQuote');
        });
    }
}
