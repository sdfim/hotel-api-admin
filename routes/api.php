<?php

use Illuminate\Support\Facades\Route;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Controllers\RouteApiController;
use Modules\API\Controllers\RouteBookingApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'content'], function () {
	Route::post('/search', [RouteApiController::class, 'handle'])->name('search');
	Route::get('/detail', [RouteApiController::class, 'handle'])->name('detail');
	Route::get('/destinations', [RouteApiController::class, 'destinations'])->name('destinations');
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'pricing'], function () {
	Route::post('/search', [RouteApiController::class, 'handle'])->name('price');
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'booking'], function () {
	Route::post('/add-item',	[RouteBookingApiController::class, 'handle'])->name('addItem');
	Route::delete('/remove-item', 	[RouteBookingApiController::class, 'handle'])->name('removeItem');
	Route::get('/retrieve-items', [BookApiHandler::class, 'retrieveItems'])->name('retrieveItems');
	Route::post('/add-passengers', 	[BookApiHandler::class, 'addPassengers'])->name('addPassengers');

	Route::post('/book', [BookApiHandler::class, 'book'])->name('book');
	Route::get('/list-bookings', [BookApiHandler::class, 'listBookings'])->name('listBookings');
	Route::put('/change-booking', [BookApiHandler::class, 'changeBooking'])->name('changeBooking');
	Route::get('/retrieve-booking', [BookApiHandler::class, 'retrieveBooking'])->name('retrieveBooking');
	Route::delete('/cancel-booking', [BookApiHandler::class, 'cancelBooking'])->name('cancelBooking');
});
