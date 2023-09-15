<?php

use App\Http\Controllers\ConfigurationChannelsController;
use App\Http\Controllers\PricingRulesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::resource('channels', ConfigurationChannelsController::class);
    Route::resource('pricing-rules', PricingRulesController::class);
    Route::get('/index', [App\Http\Controllers\HomeController::class, 'root']);
    Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('Panel');
});
