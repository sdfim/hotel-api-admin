<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\HbsiPropertyController;
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
use Modules\HotelContentRepository\Http\Controllers\HotelRoomController;
use Modules\HotelContentRepository\Http\Controllers\ImageController;
use Modules\HotelContentRepository\Http\Controllers\ImageGalleryController;
use Modules\HotelContentRepository\Http\Controllers\PdGridController;
use Modules\HotelContentRepository\Http\Controllers\ProductController;
use Modules\HotelContentRepository\Http\Controllers\VendorController;
use App\Http\Controllers\BookingEmailVerificationController;
use Modules\AdministrationSuite\Http\Controllers\PaymentInitController;
use App\Services\PdfGeneratorService;

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

Route::get('/phpinfo', fn () => phpinfo());

Route::fallback(function () {
    if (! request()->is('api/*')) {
        return redirect()->route('root');
    } else {
        return response()->json(['message' => 'Not Found'], 404);
    }
});

Route::get(
    '/admin/',
    fn () => Auth::check()
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

Route::get('/test/quote-approved', function () {
    return view('booking.email_verification_thankyou');
});

Route::get('/test/payment-email', function () {
    // Test data
    $paymentUrl = 'https://example.com/pay/ABC123';
    $hotelName = 'Casa Terra Mare';

    return view('emails.booking.client_payment', [
        'payment_url' => $paymentUrl,
        'hotelName'   => $hotelName,
    ]);
});

Route::get('/test/advisor-confirmation-email', function () {
    // Test hotel name
    $hotelName = 'Grand Velas Riviera Nayarit';

    return view('emails.booking.advisor-confirmation', [
        'hotelName' => $hotelName,
    ]);
});

Route::get('/test/client-confirmation-email', function () {
    // Test hotel name
    $hotelName = 'Grand Velas Riviera Nayarit';

    return view('emails.booking.client-confirmation', [
        'hotelName' => $hotelName,
    ]);
});

Route::get('/test/quote-email', function () {
    // Fake hotel object (structure similar to real model)
    $hotel = (object) [
        'product' => (object) [
            'name'       => 'Grand Velas Riviera Nayarit',
            // If you have some hero image in storage you can put its path here
            'hero_image' => 'hotel.jpg',
        ],
        'address' => [
            'Av Cocoteros 98 Sur',
            'Nuevo Vallarta',
            'Nayarit, 63735',
            'Mexico',
        ],
        'star_rating' => 5,
        // Optional board basis example
        'hotel_board_basis' => 'All-Inclusive Meal Plan',
    ];

    // Fake rooms (keep structure close to real one)
    $rooms = [
        [
            'total_net'   => 2378.79,
            'total_tax'   => 570.54,
            'total_fees'  => 0,
            'total_price' => 2949.33,
            'agent_commission' => 328.11,
            'currency'    => 'USD',

            // Cancellation policies for "Refundable until"
            'cancellation_policies' => [
                [
                    'description'        => 'General Cancellation Policy',
                    'penalty_start_date' => '2026-04-23',
                ],
            ],

            // Meal plan from room level
            'meal_plans_available' => 'All-Inclusive Meal Plan',

            // Optional room image (if you want to test it, put something in storage and set the path)
            'room_image' => null,
        ],
    ];

    // Fake search request
    $searchRequest = [
        'checkin'   => '2026-04-26',
        'checkout'  => '2026-04-30',
        'occupancy' => [
            [
                'adults'         => 2,
                'children_ages'  => [],
            ],
        ],
    ];

    // Fake perks list
    $perks = [
        '200 USD Hotel Credit',
        'Complimentary Hydrotherapy Water Journey',
        'Daily breakfast for up to two guests per bedroom',
        'Early Check-In / Late Check-Out, subject to availability',
        'Complimentary Wi-Fi',
    ];

    // Fake verification URL
    $verificationUrl = 'https://example.com/quotes/123/confirm';

    return view('emails.booking.email_verification', [
        'hotel'           => $hotel,
        'rooms'           => $rooms,
        'searchRequest'   => $searchRequest,
        'perks'           => $perks,
        'verificationUrl' => $verificationUrl,
    ]);
});

Route::get('/test/pdf-quote', function () {

    // Fake / test data for Quote PDF
    $pdfData = [
        'hotel' => null,
        'hotelData' => [
            'name'    => 'Grand Velas Riviera Nayarit',
            'address' => 'Av Cocoteros 98 Sur, Nuevo Vallarta, Nayarit, 63735',
        ],
        'total_net'   => 2378.79,
        'total_tax'   => 570.54,
        'total_fees'  => 0,
        'total_price' => 2949.33,
        'agency' => [
            'booking_agent'       => 'Test Agent',
            'booking_agent_email' => 'test@terramare.com',
        ],
        'hotelPhotoPath'    => asset('storage/hotel.jpg'),
        'confirmation_date' => now()->toDateString(),

        // Optional fields for new design
        'checkin'        => '04/26/2026',
        'checkout'       => '04/30/2026',
        'guest_info'     => '1 Room(s), 2 Adults, 0 Children',
        'main_room_name' => 'Terrace Room Suite',
        'rate_refundable'=> 'Refundable until 04/23/2026',
        'rate_meal_plan' => 'All-Inclusive Meal Plan',
        'currency'       => 'USD',
        'taxes_and_fees' => 570.54,
        'perks' => [
            '$200 USD Hotel Credit',
            'Upgrade on arrival, subject to availability',
            'Daily breakfast for up to two guests per bedroom',
            'Complimentary Hydrotherapy Water Journey',
            '$50 USD Spa Credit, per adult, per night',
            '$50 USD Credit in private experiences, once per stay',
            'Early Check-In / Late Check-Out, subject to availability',
            'Complimentary Wi-Fi',
        ],
    ];

    /** @var PdfGeneratorService $pdfService */
    $pdfService = app(PdfGeneratorService::class);
    $pdfContent = $pdfService->generateRaw('pdf.quote-confirmation', $pdfData);

    return response($pdfContent)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="test-quote.pdf"');
});

/**
 * Test route for Advisor Confirmation PDF (with Advisor Commission line).
 */
Route::get('/test/pdf-advisor-confirmation', function () {

    $pdfData = [
        'hotel' => null,
        'hotelData' => [
            'name'    => 'Grand Velas Riviera Nayarit',
            'address' => 'Av Cocoteros 98 Sur, Nuevo Vallarta, Nayarit, 63735',
        ],
        'total_net'   => 2378.79,
        'total_tax'   => 570.54,
        'total_fees'  => 0,
        'total_price' => 2949.33,
        'agency' => [
            'booking_agent'       => 'Test Agent',
            'booking_agent_email' => 'test@terramare.com',
        ],
        'hotelPhotoPath'    => asset('storage/hotel.jpg'),
        'confirmation_date' => now()->toDateString(),

        // Layout fields
        'checkin'        => '04/26/2026',
        'checkout'       => '04/30/2026',
        'guest_info'     => '1 Room(s), 2 Adults, 0 Children',
        'main_room_name' => 'Terrace Room Suite',
        'rate_refundable'=> 'Refundable until 04/23/2026',
        'rate_meal_plan' => 'All-Inclusive Meal Plan',
        'currency'       => 'USD',
        'taxes_and_fees' => 570.54,
        'perks' => [
            '$200 USD Hotel Credit',
            'Upgrade on arrival, subject to availability',
            'Daily breakfast for up to two guests per bedroom',
            'Complimentary Hydrotherapy Water Journey',
            '$50 USD Spa Credit, per adult, per night',
            '$50 USD Credit in private experiences, once per stay',
            'Early Check-In / Late Check-Out, subject to availability',
            'Complimentary Wi-Fi',
        ],

        'advisor_commission'  => 512.67,
        'confirmation_number' => '297697W7Lexp',
    ];

    /** @var PdfGeneratorService $pdfService */
    $pdfService = app(PdfGeneratorService::class);
    $pdfContent = $pdfService->generateRaw('pdf.advisor-confirmation', $pdfData);

    return response($pdfContent)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="test-advisor-confirmation.pdf"');
});

/**
 * Test route for Client Confirmation PDF (2 pages: welcome + summary).
 * Advisor Commission is not shown in this PDF.
 */
Route::get('/test/pdf-client-confirmation', function () {

    $pdfData = [
        'hotel' => null,
        'hotelData' => [
            'name'    => 'Grand Velas Riviera Nayarit',
            'address' => 'Av Cocoteros 98 Sur, Nuevo Vallarta, Nayarit, 63735',
        ],
        'total_net'   => 2378.79,
        'total_tax'   => 570.54,
        'total_fees'  => 0,
        'total_price' => 2949.33,
        'agency' => [
            'booking_agent'       => 'Test Agent',
            'booking_agent_email' => 'test@terramare.com',
        ],
        'hotelPhotoPath'    => asset('storage/hotel.jpg'),
        'confirmation_date' => now()->toDateString(),

        // Layout fields
        'checkin'        => '04/26/2026',
        'checkout'       => '04/30/2026',
        'guest_info'     => '1 Room(s), 2 Adults, 0 Children',
        'main_room_name' => 'Terrace Room Suite',
        'rate_refundable'=> 'Refundable until 04/23/2026',
        'rate_meal_plan' => 'All-Inclusive Meal Plan',
        'currency'       => 'USD',
        'taxes_and_fees' => 570.54,
        'perks' => [
            '$200 USD Hotel Credit',
            'Upgrade on arrival, subject to availability',
            'Daily breakfast for up to two guests per bedroom',
            'Complimentary Hydrotherapy Water Journey',
            '$50 USD Spa Credit, per adult, per night',
            '$50 USD Credit in private experiences, once per stay',
            'Early Check-In / Late Check-Out, subject to availability',
            'Complimentary Wi-Fi',
        ],

        'confirmation_number' => '297697W7Lexp',
    ];

    /** @var PdfGeneratorService $pdfService */
    $pdfService = app(PdfGeneratorService::class);
    $pdfContent = $pdfService->generateRaw('pdf.client-confirmation', $pdfData);

    return response($pdfContent)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="test-client-confirmation.pdf"');
});
