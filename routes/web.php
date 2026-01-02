<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BookingEmailVerificationController;
use App\Http\Controllers\HbsiPropertyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TeamController;
use App\Http\Middleware\SelectTeamAfterAcceptMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController;
use Modules\AdministrationSuite\Http\Controllers\BookingInspectorController;
use Modules\AdministrationSuite\Http\Controllers\BookingItemsController;
use Modules\AdministrationSuite\Http\Controllers\ChannelsController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigAmenityController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigAttributeCategoryController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigAttributeController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigChainController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigCommissionController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigConsortiumController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigContactInformationDepartmentController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigDescriptiveTypeController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigInsuranceDocumentationTypeController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigJobDescriptionController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigKeyMappingOwnerController;
use Modules\AdministrationSuite\Http\Controllers\Configurations\ConfigRoomBedTypeController;
use Modules\AdministrationSuite\Http\Controllers\ContentController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportChartController;
use Modules\AdministrationSuite\Http\Controllers\ExceptionsReportController;
use Modules\AdministrationSuite\Http\Controllers\ExpediaController;
use Modules\AdministrationSuite\Http\Controllers\GeneralConfigurationController;
use Modules\AdministrationSuite\Http\Controllers\GeographyController;
use Modules\AdministrationSuite\Http\Controllers\HiltonPropertyController;
use Modules\AdministrationSuite\Http\Controllers\HotelTraderController;
use Modules\AdministrationSuite\Http\Controllers\IcePortalController;
use Modules\AdministrationSuite\Http\Controllers\MappingExpediaGiatasController;
use Modules\AdministrationSuite\Http\Controllers\OracleContentController;
use Modules\AdministrationSuite\Http\Controllers\PaymentInitController;
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
use Modules\HotelContentRepository\API\Controllers\TravelAgencyCommissionController;
use Modules\HotelContentRepository\Http\Controllers\HotelController;
use Modules\HotelContentRepository\Http\Controllers\HotelRoomController;
use Modules\HotelContentRepository\Http\Controllers\ImageController;
use Modules\HotelContentRepository\Http\Controllers\ImageGalleryController;
use Modules\HotelContentRepository\Http\Controllers\PdGridController;
use Modules\HotelContentRepository\Http\Controllers\ProductController;
use Modules\HotelContentRepository\Http\Controllers\VendorController;
use Illuminate\Http\Request;
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

// Временный роут для диагностики кеша Watchdog и контента
Route::get('/debug/cache-check', function (Request $request) {
    // Получаем search_id из параметра запроса
    $searchId = $request->query('id');

    if (empty($searchId)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Необходимо передать параметр "id" (search_id) в URL.',
        ], 400);
    }

    $keysToCheck = [
        'watchdog_key_for_inspector',
        'dataOriginal',
        'content',
        'clientContent',
        'clientContentWithPricingRules',
    ];

    $results = [];

    foreach ($keysToCheck as $keyName) {
        $key = $keyName.'_'.$searchId;
        // Используем Cache::get() для проверки наличия и получения данных
        $data = Cache::get($key);
        $exists = ! is_null($data);

        $results[$key] = [
            'exists' => $exists,
            'status' => $exists ? 'Присутствует' : 'ОТСУТСТВУЕТ (Истек TTL)',
            'data_type' => $keyName === 'watchdog_key_for_inspector' ? 'Boolean (10 мин)' : 'GZ-Compressed JSON (10 мин)',
        ];

        // Попытка декомпрессии для ключей контента (ключи 2-5)
        if ($exists && $keyName !== 'watchdog_key_for_inspector') {
            try {
                // Распаковка данных (gzuncompress)
                $uncompressed = gzuncompress($data);
                // Декодирование JSON
                $content = json_decode($uncompressed, true);

                $results[$key]['decompression_status'] = 'OK';
                $results[$key]['data_summary'] = [
                    'compressed_size_bytes' => strlen($data),
                    'uncompressed_size_bytes' => strlen($uncompressed),
                    'top_level_keys' => array_keys((array) $content),
                ];
            } catch (\Throwable $e) {
                $results[$key]['decompression_status'] = 'FAILED: '.$e->getMessage();
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'search_id' => $searchId,
        'check_time' => now()->toDateTimeString(),
        'keys' => $results,
    ]);
});

Route::get('/phpinfo', fn () => phpinfo());

Route::fallback(function () {
    if (! request()->is('api/*')) {
        return redirect()->route('root');
    } else {
        return response()->json(['message' => 'Not Found'], 404);
    }
});

Route::get('/admin/', fn () => Auth::check()
    ? redirect()->route('reservations.index')
    : redirect()->route('login')
)->name('root');

Route::get('/clear-cookies-and-login', function () {
    session()->flush();
    Cookie::queue(Cookie::forget('XSRF-TOKEN'));
    Cookie::queue(Cookie::forget('laravel_session'));
    foreach (request()->cookies as $key => $value) {
        if (str_starts_with($key, 'remember_web_')) {
            Cookie::queue(Cookie::forget($key));
        }
    }

    return redirect()->route('login');
})->name('clear.cookies.and.login');

Route::post('/teams/switch', [TeamController::class, 'switch'])->name('teams.switch');

Route::get('team-invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])
    ->middleware(SelectTeamAfterAcceptMiddleware::class);

Route::prefix('admin')->group(function () {
    Route::middleware(['auth', config('jetstream.auth_session'), 'verified'])->group(function () {

        Route::resource('teams', TeamController::class)->only(['index', 'edit']);

        Route::resource('activities', ActivityController::class)->only(['index', 'show']);

        Route::resource('channels', ChannelsController::class);
        Route::resource('pricing-rules', PricingRulesController::class);
        Route::resource('suppliers', SuppliersController::class);

        Route::get('/content', [ContentController::class, 'index'])->name('content');
        Route::get('/general-configuration', [GeneralConfigurationController::class, 'index'])->name('general_configuration');
        Route::get('/geography', [GeographyController::class, 'index'])->name('geography');

        Route::resource('search-inspector', SearchInspectorController::class)->only(['index', 'show']);
        Route::resource('booking-inspector', BookingInspectorController::class)->only(['index', 'show']);
        Route::resource('payment-inspector', PaymentInitController::class)->only(['index']);

        Route::resource('booking-items', BookingItemsController::class)->only(['index', 'show']);
        Route::resource('exceptions-report', ExceptionsReportController::class)->only('index');
        Route::resource('exceptions-report-chart', ExceptionsReportChartController::class)->only('index');

        Route::get('/property-mapping', [PropertiesController::class, 'index'])->name('property_mapping');
        Route::resource('reservations', ReservationsController::class)->only(['index', 'show']);
        Route::resource('property-weighting', PropertyWeightingController::class)->only(['index', 'create', 'show', 'edit']);
        Route::resource('properties', PropertiesController::class)->only('index');

        Route::resource('ice-portal', IcePortalController::class)->only('index');
        Route::resource('hbsi-property', HbsiPropertyController::class)->only('index');
        Route::resource('hotel-trader', HotelTraderController::class)->only('index');
        Route::resource('expedia', ExpediaController::class)->only('index');
        Route::resource('hilton', HiltonPropertyController::class)->only('index');
        Route::resource('oracle', OracleContentController::class)->only('index');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');

        Route::get('/statistic-charts', [StatisticChartsController::class, 'index'])->name('statistic-charts');
        Route::resource('mapping', MappingExpediaGiatasController::class)->only(['store', 'destroy']);

        Route::resource('users', UsersController::class)->only(['index', 'edit', 'create']);
        Route::resource('roles', RolesController::class)->only(['index', 'edit', 'create']);
        Route::get('permissions', PermissionsController::class)->name('permissions.index');

        Route::resource('hotel-repository', HotelController::class);
        Route::resource('product-repository', ProductController::class);
        Route::resource('vendor-repository', VendorController::class);
        Route::resource('hotel-rooms', HotelRoomController::class)->only(['index']);
        Route::resource('travel-agency-commission', TravelAgencyCommissionController::class);

        Route::resource('pd-grid', PdGridController::class)->only(['index']);

        Route::prefix('configurations')->name('configurations.')->group(function () {
            Route::resource('attributes', ConfigAttributeController::class)->only(['index', 'create', 'edit']);
            Route::resource('attribute-categories', ConfigAttributeCategoryController::class)->only(['index', 'create', 'edit']);
            Route::resource('amenities', ConfigAmenityController::class)->only(['index', 'create', 'edit']);
            Route::resource('consortia', ConfigConsortiumController::class)->only(['index', 'create', 'edit']);
            Route::resource('descriptive-types', ConfigDescriptiveTypeController::class)->only(['index', 'create', 'edit']);
            Route::resource('job-descriptions', ConfigJobDescriptionController::class)->only(['index', 'create', 'edit']);
            Route::resource('room-bed-types', ConfigRoomBedTypeController::class)->only(['index', 'create', 'edit']);
            Route::resource('contact-information-departments', ConfigContactInformationDepartmentController::class)->only(['index', 'create', 'edit']);
            Route::resource('chains', ConfigChainController::class)->only(['index', 'create', 'edit']);
            Route::resource('insurance-documentation-types', ConfigInsuranceDocumentationTypeController::class)->only(['index', 'create', 'edit']);
            Route::resource('external-identifiers', ConfigKeyMappingOwnerController::class)->only(['index', 'create', 'edit']);
            Route::resource('commissions', ConfigCommissionController::class)->only(['index', 'create', 'edit']);

        });

        Route::resource('image-galleries', ImageGalleryController::class)->only(['index', 'create', 'edit']);
        Route::resource('images', ImageController::class)->only(['index', 'create', 'edit']);

        // AirwallexApiLog table route
        Route::get('airwallex-api-logs', [\App\Http\Controllers\AirwallexApiLogController::class, 'index'])->name('airwallex-api-logs.index');

        Route::get('/index', [App\Http\Controllers\HomeController::class, 'root']);
        Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('Panel');
    });
});

Route::get('/booking/verify/{booking_item}/{uuid}', [BookingEmailVerificationController::class, 'verify'])->name('booking.verify');
Route::get('/booking/deny/{booking_item}/{uuid}', [BookingEmailVerificationController::class, 'deny'])->name('booking.deny');
