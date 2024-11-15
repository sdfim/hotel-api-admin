<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\HotelContentRepository\Livewire\Hotel\HotelForm;
use Modules\HotelContentRepository\Livewire\Hotel\HotelTable;
use Modules\HotelContentRepository\Livewire\HotelAffiliations\HotelAffiliationsTable;
use Modules\HotelContentRepository\Livewire\HotelAgeRestriction\HotelAgeRestrictionTable;
use Modules\HotelContentRepository\Livewire\HotelAttributes\HotelAttributesTable;
use Modules\HotelContentRepository\Livewire\HotelContactInformation\HotelContactInformationTable;
use Modules\HotelContentRepository\Livewire\HotelDepositInformation\HotelDepositInformationTable;
use Modules\HotelContentRepository\Livewire\HotelDescriptiveContentSection\HotelDescriptiveContentSectionTable;
use Modules\HotelContentRepository\Livewire\HotelFeeTaxes\HotelFeeTaxTable;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesForm;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesTable;
use Modules\HotelContentRepository\Livewire\HotelInformativeServices\HotelInformativeServicesTable;
use Modules\HotelContentRepository\Livewire\HotelPromotion\HotelPromotionTable;
use Modules\HotelContentRepository\Livewire\HotelRooms\HotelRoomTable;
use Modules\HotelContentRepository\Livewire\HotelWebFinder\HotelWebFinderTable;
use Modules\HotelContentRepository\Livewire\ImageGalleries\ImageGalleriesForm;
use Modules\HotelContentRepository\Livewire\ImageGalleries\ImageGalleriesTable;
use Modules\HotelContentRepository\Livewire\KeyMappings\KeyMappingTable;
use Modules\HotelContentRepository\Livewire\TravelAgencyCommission\TravelAgencyCommissionTable;
use Modules\Insurance\Livewire\Plans\InsurancePlanTable;
use Modules\Insurance\Livewire\Providers\ProvidersDocumentationTable;
use Modules\Insurance\Livewire\Providers\ProvidersTable;
use Modules\Insurance\Livewire\RateTiers\RateTiersTable;
use Modules\Insurance\Livewire\Restrictions\RestrictionsTable;

class SupplierRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register any application services.
    }

    public function boot(): void
    {
        $this->registerContentRepositoryComponents();
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
        Livewire::component('hotels.hotel-descriptive-content-section-table', HotelDescriptiveContentSectionTable::class);
        Livewire::component('hotels.hotel-promotion-table', HotelPromotionTable::class);
        Livewire::component('hotels.hotel-deposit-information-table', HotelDepositInformationTable::class);
        Livewire::component('hotels.hotel-contact-information-table', HotelContactInformationTable::class);
        Livewire::component('image-galleries.image-galleries-table', ImageGalleriesTable::class);
        Livewire::component('image-galleries.image-galleries-form', ImageGalleriesForm::class);
        Livewire::component('hotel-images.hotel-images-table', HotelImagesTable::class);
        Livewire::component('hotel-images.hotel-images-form', HotelImagesForm::class);
        Livewire::component('hotels.hotel-web-finder-table', HotelWebFinderTable::class);
        Livewire::component('insurance.providers-table', ProvidersTable::class);
        Livewire::component('insurance.providers-documentation-table', ProvidersDocumentationTable::class);
        Livewire::component('insurance.restrictions-table', RestrictionsTable::class);
        Livewire::component('insurance.rate-tiers-table', RateTiersTable::class);
        Livewire::component('insurance.insurance-plans-table', InsurancePlanTable::class);
    }
}
