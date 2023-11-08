<?php

use Modules\AdministrationSuite\Http\Controllers\ContentController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportChartController;
use Modules\AdministrationSuite\Http\Controllers\GeneralConfigurationController;
use Modules\AdministrationSuite\Http\Controllers\GeographyController;
use Modules\AdministrationSuite\Http\Controllers\SearchInspectorController;
use Modules\AdministrationSuite\Http\Controllers\BookingInspectorController;
use Modules\AdministrationSuite\Http\Controllers\PricingRulesController;
use Modules\AdministrationSuite\Http\Controllers\PropertyMappingController;
use Modules\AdministrationSuite\Http\Controllers\ReservationsController;
use Modules\AdministrationSuite\Http\Controllers\PropertyWeightingController;
use Modules\AdministrationSuite\Http\Controllers\ChannelsController;
use Modules\AdministrationSuite\Http\Controllers\SuppliersController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Modules\AdministrationSuite\Http\Controllers\ExpediaController;
use Modules\AdministrationSuite\Http\Controllers\GiataController;
use Modules\AdministrationSuite\Http\Controllers\MappingExpediaGiatasController;

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
        return redirect('/admin/login');
    } else {
        return redirect('/admin/reservations');
    }
});

Route::prefix('admin')->group(function () {
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
        Route::resource('channels', ChannelsController::class);
        Route::resource('pricing_rules', PricingRulesController::class);
        Route::resource('suppliers', SuppliersController::class);

        Route::get('/content', [ContentController::class, 'index'])->name('content');
        Route::get('/general-configuration', [GeneralConfigurationController::class, 'index'])->name('general_configuration');
        Route::get('/geography', [GeographyController::class, 'index'])->name('geography');

        Route::resource('search-inspector', SearchInspectorController::class)->except(['delete', 'store', 'create', 'update', 'destroy', 'edit']);
        Route::resource('booking-inspector', BookingInspectorController::class)->except(['delete', 'store', 'create', 'update', 'destroy', 'edit']);
        Route::resource('exceptions-report', ExceptionsReportController::class)->except(['delete', 'store', 'create', 'update', 'destroy', 'edit']);
        Route::resource('exceptions-report-chart', ExceptionsReportChartController::class)->except(['delete', 'store', 'create', 'update', 'destroy', 'edit']);

        Route::get('/property-mapping', [PropertyMappingController::class, 'index'])->name('property_mapping');
        Route::resource('reservations', ReservationsController::class)->except(['delete', 'store', 'create']);
        Route::resource('property-weighting', PropertyWeightingController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('giata', GiataController::class)->except(['delete', 'store', 'create']);
        Route::resource('expedia', ExpediaController::class)->except(['delete', 'store', 'create']);
        Route::get('/expedia-charts', [ExpediaController::class, 'charts'])->name('expedia_charts');
        Route::resource('mapping', MappingExpediaGiatasController::class)->except(['index', 'update', 'create']);

        Route::get('/index', [App\Http\Controllers\HomeController::class, 'root']);
        Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('Panel');
    });
});
