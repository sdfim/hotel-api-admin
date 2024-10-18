<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\AdministrationSuite\Http\Controllers\BookingInspectorController;
use Modules\AdministrationSuite\Http\Controllers\BookingItemsController;
use Modules\AdministrationSuite\Http\Controllers\ChannelsController;
use Modules\AdministrationSuite\Http\Controllers\ContentController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportChartController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportController;
use Modules\AdministrationSuite\Http\Controllers\ExpediaController;
use Modules\AdministrationSuite\Http\Controllers\GeneralConfigurationController;
use Modules\AdministrationSuite\Http\Controllers\GeographyController;
use Modules\AdministrationSuite\Http\Controllers\IceHbsiController;
use Modules\AdministrationSuite\Http\Controllers\MappingExpediaGiatasController;
use Modules\AdministrationSuite\Http\Controllers\PermissionsController;
use Modules\AdministrationSuite\Http\Controllers\PricingRulesController;
use Modules\AdministrationSuite\Http\Controllers\PropertiesController;
use Modules\AdministrationSuite\Http\Controllers\PropertyWeightingController;
use Modules\AdministrationSuite\Http\Controllers\ReservationsController;
use Modules\AdministrationSuite\Http\Controllers\RolesController;
use Modules\AdministrationSuite\Http\Controllers\SearchInspectorController;
use Modules\AdministrationSuite\Http\Controllers\StatisticChartsController;
use Modules\AdministrationSuite\Http\Controllers\SuppliersController;
use Modules\AdministrationSuite\Http\Controllers\UsersController;

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

Route::get('/admin/', function () {
    if (! Auth::check()) {
        return redirect(config('app.url').'/admin/login');
    } else {
        return redirect(config('app.url').'/admin/reservations');
    }
})->name('root');

Route::prefix('admin')->group(function () {
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
        Route::resource('channels', ChannelsController::class);
        Route::resource('pricing-rules', PricingRulesController::class);
        Route::resource('suppliers', SuppliersController::class);

        Route::get('/content', [ContentController::class, 'index'])->name('content');
        Route::get('/general-configuration', [GeneralConfigurationController::class, 'index'])->name('general_configuration');
        Route::get('/geography', [GeographyController::class, 'index'])->name('geography');

        Route::resource('search-inspector', SearchInspectorController::class)->only(['index', 'show']);
        Route::resource('booking-inspector', BookingInspectorController::class)->only(['index', 'show']);
        Route::resource('booking-items', BookingItemsController::class)->only(['index', 'show']);
        Route::resource('exceptions-report', ExceptionsReportController::class)->only('index');
        Route::resource('exceptions-report-chart', ExceptionsReportChartController::class)->only('index');

        Route::get('/property-mapping', [PropertiesController::class, 'index'])->name('property_mapping');
        Route::resource('reservations', ReservationsController::class)->only(['index', 'show']);
        Route::resource('property-weighting', PropertyWeightingController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('properties', PropertiesController::class)->only('index');
        Route::resource('ice-hbsi', IceHbsiController::class)->only('index');
        Route::resource('expedia', ExpediaController::class)->only('index');
        Route::get('/statistic-charts', [StatisticChartsController::class, 'index'])->name('statistic-charts');
        Route::resource('mapping', MappingExpediaGiatasController::class)->only(['store', 'destroy']);

        Route::resource('users', UsersController::class)->only(['index', 'edit']);
        Route::resource('roles', RolesController::class)->only(['index', 'edit', 'create']);
        Route::get('permissions', PermissionsController::class)->name('permissions.index');

        Route::get('/index', [App\Http\Controllers\HomeController::class, 'root']);
        Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('Panel');
    });
});
