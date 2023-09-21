<?php

use Modules\AdministrationSuite\Http\Controllers\ContentController;
use Modules\AdministrationSuite\Http\Controllers\ContentLoaderExceptionsController;
use Modules\AdministrationSuite\Http\Controllers\GeneralConfigurationController;
use Modules\AdministrationSuite\Http\Controllers\GeographyController;
use Modules\AdministrationSuite\Http\Controllers\InspectorController;
use Modules\AdministrationSuite\Http\Controllers\PricingRulesController;
use Modules\AdministrationSuite\Http\Controllers\PropertyMappingController;
use Modules\AdministrationSuite\Http\Controllers\ReservationsController;
use Modules\AdministrationSuite\Http\Controllers\ConfigurationChannelsController;
use Modules\AdministrationSuite\Http\Controllers\SuppliersController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    if (!Auth::check()) {
        return redirect('/login');
    } else {
        return redirect('/reservations');
    }
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::resource('channels', ConfigurationChannelsController::class);
    Route::resources([
        'pricing_rules' => PricingRulesController::class,
        'suppliers' => SuppliersController::class
    ]);

    Route::get('/content-loader-exceptions', [ContentLoaderExceptionsController::class, 'index'])->name('content_loader_exceptions');
    Route::get('/content', [ContentController::class, 'index'])->name('content');
    Route::get('/general-configuration', [GeneralConfigurationController::class, 'index'])->name('general_configuration');
    Route::post('/general-configuration/save', [GeneralConfigurationController::class, 'save'])->name('general_configuration_save');
    Route::get('/geography', [GeographyController::class, 'index'])->name('geography');
    Route::get('/inspector', [InspectorController::class, 'index'])->name('inspector');
    // Route::get('/pricing-rules', [PricingRulesController::class, 'index'])->name('pricing_rules');
    Route::get('/property-mapping', [PropertyMappingController::class, 'index'])->name('property_mapping');
    Route::resource('reservations', ReservationsController::class)->except(['delete', 'store', 'create']);

    Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('Panel');
});
