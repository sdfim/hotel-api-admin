<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\HotelContentRepository\Livewire\Activity\ActivityTable;
use Modules\HotelContentRepository\Livewire\Hotel\HotelForm;
use Modules\HotelContentRepository\Livewire\Hotel\HotelTable;
use Modules\HotelContentRepository\Livewire\PdGrid\PdGridTable;
use Modules\HotelContentRepository\Livewire\Product\ProductTable;
use Modules\HotelContentRepository\Livewire\ProductAffiliations\ProductAffiliationsTable;
use Modules\HotelContentRepository\Livewire\ProductAgeRestriction\ProductAgeRestrictionTable;
use Modules\HotelContentRepository\Livewire\ProductAttributes\ProductAttributesTable;
use Modules\HotelContentRepository\Livewire\ContactInformation\ContactInformationTable;
use Modules\HotelContentRepository\Livewire\ProductDepositInformation\ProductDepositInformationTable;
use Modules\HotelContentRepository\Livewire\ProductDescriptiveContentSection\ProductDescriptiveContentSectionTable;
use Modules\HotelContentRepository\Livewire\ProductFeeTaxes\ProductFeeTaxTable;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesForm;
use Modules\HotelContentRepository\Livewire\HotelImages\HotelImagesTable;
use Modules\HotelContentRepository\Livewire\ProductInformativeServices\ProductInformativeServicesTable;
use Modules\HotelContentRepository\Livewire\ProductPromotion\ProductPromotionTable;
use Modules\HotelContentRepository\Livewire\HotelRooms\HotelRoomTable;
use Modules\HotelContentRepository\Livewire\HotelWebFinder\HotelWebFinderTable;
use Modules\HotelContentRepository\Livewire\ImageGalleries\ImageGalleriesForm;
use Modules\HotelContentRepository\Livewire\ImageGalleries\ImageGalleriesTable;
use Modules\HotelContentRepository\Livewire\KeyMappings\KeyMappingTable;
use Modules\HotelContentRepository\Livewire\TravelAgencyCommission\TravelAgencyCommissionTable;
use Modules\HotelContentRepository\Livewire\Vendor\VendorForm;
use Modules\HotelContentRepository\Livewire\Vendor\VendorTable;
use Modules\Insurance\Livewire\Plans\InsurancePlanTable;
use Modules\Insurance\Livewire\Vendors\DocumentationsTable;
use Modules\Insurance\Livewire\Vendors\ProvidersTable;
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
        Livewire::component('vendors.vendor-table', VendorTable::class);
        Livewire::component('vendors.vendor-form', VendorForm::class);

        Livewire::component('products.pd-grid-table', PdGridTable::class);

        Livewire::component('products.product-table', ProductTable::class);
        Livewire::component('products.key-mapping-table', KeyMappingTable::class);
        Livewire::component('products.product-affiliations-table', ProductAffiliationsTable::class);
        Livewire::component('products.hotel-age-restriction-table', ProductAgeRestrictionTable::class);
        Livewire::component('products.product-attributes-table', ProductAttributesTable::class);
        Livewire::component('products.contact-information-table', ContactInformationTable::class);
        Livewire::component('products.product-deposit-information-table', ProductDepositInformationTable::class);
        Livewire::component('products.hotel-descriptive-content-section-table', ProductDescriptiveContentSectionTable::class);
        Livewire::component('products.hotel-fee-tax-table', ProductFeeTaxTable::class);
        Livewire::component('products.product-informative-services-table', ProductInformativeServicesTable::class);
        Livewire::component('products.hotel-promotion-table', ProductPromotionTable::class);

        Livewire::component('hotels.hotel-form', HotelForm::class);
        Livewire::component('hotels.hotel-table', HotelTable::class);
        Livewire::component('hotels.hotel-web-finder-table', HotelWebFinderTable::class);
        Livewire::component('hotels.hotel-room-table', HotelRoomTable::class);

        Livewire::component('commissions.travel-agency-commission-table', TravelAgencyCommissionTable::class);

        Livewire::component('image-galleries.image-galleries-table', ImageGalleriesTable::class);
        Livewire::component('image-galleries.image-galleries-form', ImageGalleriesForm::class);
        Livewire::component('hotel-images.hotel-images-table', HotelImagesTable::class);
        Livewire::component('hotel-images.hotel-images-form', HotelImagesForm::class);

        Livewire::component('insurance.documentation-table', DocumentationsTable::class);
        Livewire::component('insurance.restrictions-table', RestrictionsTable::class);
        Livewire::component('insurance.rate-tiers-table', RateTiersTable::class);
        Livewire::component('insurance.insurance-plans-table', InsurancePlanTable::class);

        Livewire::component('activity.activity-table', ActivityTable::class);
    }
}
