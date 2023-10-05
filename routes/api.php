<?php

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\RoteApiController;
use Modules\API\Controllers\TestAsyncGuzzle;
;

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
	Route::post('/search', [RoteApiController::class, 'handle'])->name('search');
	Route::get('/detail', [RoteApiController::class, 'handle'])->name('detail');
});

Route::group(['middleware' => 'auth:sanctum'], function () {

	Route::post('/price', [RoteApiController::class, 'handle'])->name('price');
});

Route::group(['middleware' => 'auth:sanctum'], function () {

	Route::get('/test', [TestAsyncGuzzle::class, 'test']);
	Route::get('/testSync', [TestAsyncGuzzle::class, 'testSync']);
	Route::get('/testAsync', [TestAsyncGuzzle::class, 'testAsync']);
});

