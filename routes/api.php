<?php

use Illuminate\Support\Facades\Route;
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
	Route::get('/retrieve-items', [RouteBookingApiController::class, 'handle'])->name('retrieveItems');
	Route::put('/change-items', [RouteBookingApiController::class, 'handle'])->name('changeItems');
	Route::post('/add-passengers', 	[RouteBookingApiController::class, 'handle'])->name('addPassengers');
	Route::post('/book',			[RouteBookingApiController::class, 'handle'])->name('book');
	Route::get('/list-bookings', 	[RouteBookingApiController::class, 'handle'])->name('listBookings');
	Route::post('/retrieve-booking', 	[RouteBookingApiController::class, 'handle'])->name('retrieveBooking');
	Route::post('/cancel-booking', 	[RouteBookingApiController::class, 'handle'])->name('cancelBooking');
});
