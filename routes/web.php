<?php

use Modules\AdministrationSuite\Http\Controllers\ContentController;
use Modules\AdministrationSuite\Http\Controllers\ContentLoaderExceptionsController;
use Modules\AdministrationSuite\Http\Controllers\GeneralConfigurationController;
use Modules\AdministrationSuite\Http\Controllers\ChanelsConfigurationController;
use Modules\AdministrationSuite\Http\Controllers\GeographyController;
use Modules\AdministrationSuite\Http\Controllers\InspectorController;
use Modules\AdministrationSuite\Http\Controllers\PricingRulesController;
use Modules\AdministrationSuite\Http\Controllers\PropertyMappingController;
use Modules\AdministrationSuite\Http\Controllers\ReservationsController;
use Modules\AdministrationSuite\Http\Controllers\ConfigurationChannelsController;
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
    Route::get('/chanels-configuration', [ChanelsConfigurationController::class, 'index'])->name('chanels_configuration');
    Route::get('/content-loader-exceptions', [ContentLoaderExceptionsController::class, 'index'])->name('content_loader_exceptions');
    Route::get('/content', [ContentController::class, 'index'])->name('content');
    Route::get('/general-configuration', [GeneralConfigurationController::class, 'index'])->name('general_configuration');
    Route::get('/geography', [GeographyController::class, 'index'])->name('geography');
    Route::get('/inspector', [InspectorController::class, 'index'])->name('inspector');
    // Route::get('/pricing-rules', [PricingRulesController::class, 'index'])->name('pricing_rules');
    Route::get('/property-mapping', [PropertyMappingController::class, 'index'])->name('property_mapping');
    Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations');

    Route::get('/index', [App\Http\Controllers\HomeController::class, 'root']);
    Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('Panel');
});
