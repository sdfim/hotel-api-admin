<?php

use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\AdministrationSuite\Http\Controllers\BookingInspectorController;
use Modules\AdministrationSuite\Http\Controllers\BookingItemsController;
use Modules\AdministrationSuite\Http\Controllers\ChannelsController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigAttributeController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigChainController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigConsortiumController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigDescriptiveTypeController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigJobDescriptionController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigServiceTypeController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\GroupConfigController;
use Modules\AdministrationSuite\Http\Controllers\ContentController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportChartController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportController;
use Modules\AdministrationSuite\Http\Controllers\ExpediaController;
use Modules\AdministrationSuite\Http\Controllers\GeneralConfigurationController;
use Modules\AdministrationSuite\Http\Controllers\GeographyController;
use Modules\AdministrationSuite\Http\Controllers\IceHbsiController;
use Modules\AdministrationSuite\Http\Controllers\InformationalServicesController;
use Modules\AdministrationSuite\Http\Controllers\InsuranceProvidersController;
use Modules\AdministrationSuite\Http\Controllers\InsuranceRestrictionsController;
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
use Modules\HotelContentRepository\Http\Controllers\HotelController;
use Modules\HotelContentRepository\Http\Controllers\ImageController;
use Modules\HotelContentRepository\Http\Controllers\HotelRoomController;
use Modules\HotelContentRepository\Http\Controllers\ImageGalleryController;
use Modules\HotelContentRepository\Http\Controllers\PdGridController;
use Modules\HotelContentRepository\Http\Controllers\ProductController;
use Modules\HotelContentRepository\Http\Controllers\TravelAgencyCommissionController;
use Modules\HotelContentRepository\Http\Controllers\VendorController;
use Modules\Insurance\Http\Controllers\InsurancePlansController;
use Modules\Insurance\Http\Controllers\InsuranceDocumentationsController;
use Modules\Insurance\Http\Controllers\InsuranceRateTiersController;

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
        return redirect(config('app.url').'/admin/vendor-repository');
    }
})->name('root');

Route::post('/teams/switch', [TeamController::class, 'switch'])->name('teams.switch');

Route::prefix('admin')->group(function () {
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

        Route::resource('teams', TeamController::class)->only(['index', 'edit']);

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

        Route::resource('informational-services', InformationalServicesController::class)->only(['index', 'edit', 'create']);

        Route::resource('users', UsersController::class)->only(['index', 'edit', 'create']);
        Route::resource('roles', RolesController::class)->only(['index', 'edit', 'create']);
        Route::get('permissions', PermissionsController::class)->name('permissions.index');

        Route::resource('hotel-repository', HotelController::class);
        Route::resource('product-repository', ProductController::class);
        Route::resource('vendor-repository', VendorController::class);
        Route::resource('hotel_rooms', HotelRoomController::class);
        Route::resource('travel-agency-commission', TravelAgencyCommissionController::class);

        Route::resource('pd-grid', PdGridController::class)->only(['index']);

        Route::resource('/insurance-providers-documentation', InsuranceDocumentationsController::class)->only(['index']);
        Route::resource('/insurance-restrictions', InsuranceRestrictionsController::class)->only(['index']);
        Route::resource('/insurance-rate-tiers', InsuranceRateTiersController::class)->only(['index']);
        Route::resource('/insurance-plans', InsurancePlansController::class)->only(['index']);

        Route::prefix('configurations')->name('configurations.')->group(function () {
            Route::resource('attributes', ConfigAttributeController::class)->only(['index', 'create', 'edit']);
            Route::resource('consortia', ConfigConsortiumController::class)->only(['index', 'create', 'edit']);
            Route::resource('descriptive-types', ConfigDescriptiveTypeController::class)->only(['index', 'create', 'edit']);
            Route::resource('job-descriptions', ConfigJobDescriptionController::class)->only(['index', 'create', 'edit']);
            Route::resource('service-types', ConfigServiceTypeController::class)->only(['index', 'create', 'edit']);
            Route::resource('chains', ConfigChainController::class)->only(['index', 'create', 'edit']);
        });

        Route::resource('image-galleries', ImageGalleryController::class)->only(['index', 'create', 'edit']);
        Route::resource('images', ImageController::class)->only(['index', 'create', 'edit']);

        Route::get('/index', [App\Http\Controllers\HomeController::class, 'root']);
        Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('Panel');
    });
});
