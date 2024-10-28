<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\PropertyCallFactory;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\HotelContentRepository\Livewire\Hotel\HotelForm;
use Modules\HotelContentRepository\Livewire\Hotel\HotelTable;
use Modules\HotelContentRepository\Livewire\HotelAffiliations\HotelAffiliationsTable;
use Modules\HotelContentRepository\Livewire\HotelAgeRestriction\HotelAgeRestrictionTable;
use Modules\HotelContentRepository\Livewire\HotelAttributes\HotelAttributesTable;
use Modules\HotelContentRepository\Livewire\HotelFeeTaxes\HotelFeeTaxTable;
use Modules\HotelContentRepository\Livewire\HotelInformativeServices\HotelInformativeServicesTable;
use Modules\HotelContentRepository\Livewire\HotelRooms\HotelRoomTable;
use Modules\HotelContentRepository\Livewire\KeyMappings\KeyMappingTable;
use Modules\HotelContentRepository\Livewire\TravelAgencyCommission\TravelAgencyCommissionTable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin/reservations';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RapidClient::class, function () {
            return new RapidClient();
        });

        $this->app->singleton(PropertyCallFactory::class, function ($app) {
            $rapidClient = $app->make(RapidClient::class);

            return new PropertyCallFactory($rapidClient);
        });

        $this->app->singleton(ExpediaService::class, function ($app) {
            // TODO: need to review the next two lines, as the constructor of the ExpediaService class does not have any input parameters.
            $propertyCallFactory = $app->make(PropertyCallFactory::class);

            return new ExpediaService($propertyCallFactory);
        });

		if ($this->app->environment('local')) {
            $this->app->register(HorizonServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerContentRepositoryComponents();

        $currentUrl = URL::current();
        if (! str_contains($currentUrl, 'localhost') && ! str_contains($currentUrl, '127.0.0.1')) {
            URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);

        $this->bootRoute();
    }

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

    }

    protected function registerContentRepositoryComponents(): void
    {
        Livewire::component('commissions.travel-agency-commission-table', TravelAgencyCommissionTable::class);
        Livewire::component('hotels.hotel-form', HotelForm::class);
        Livewire::component('hotels.hotel-table', HotelTable::class);
        Livewire::component('hotels.key-mapping-table', KeyMappingTable::class);
        Livewire::component('hotels.hotel-room-table', HotelRoomTable::class);
        Livewire::component('hotels.hotel-affiliations-table', HotelAffiliationsTable::class);
        Livewire::component('hotels.hotel-attributes-table', HotelAttributesTable::class);
        Livewire::component('hotels.hotel-informative-services-table', HotelInformativeServicesTable::class);
        Livewire::component('hotels.hotel-age-restriction-table', HotelAgeRestrictionTable::class);
        Livewire::component('hotels.hotel-fee-tax-table', HotelFeeTaxTable::class);
    }
}
