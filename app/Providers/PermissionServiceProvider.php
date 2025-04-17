<?php

namespace App\Providers;

use App\Models\Configurations\ConfigAmenity;
use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigAttributeCategory;
use App\Models\Configurations\ConfigChain;
use App\Models\Configurations\ConfigConsortium;
use App\Models\Configurations\ConfigDescriptiveType;
use App\Models\Configurations\ConfigInsuranceDocumentationType;
use App\Models\Configurations\ConfigJobDescription;
use App\Models\Configurations\ConfigRoomBedType;
use App\Models\Configurations\ConfigServiceType;
use App\Models\Enums\RoleSlug;
use App\Models\InformationalService;
use App\Policies\Configurations\CommissionPolicy;
use App\Policies\Configurations\ConfigAmenityPolicy;
use App\Policies\Configurations\ConfigAttributeCategoryPolicy;
use App\Policies\Configurations\ConfigAttributePolicy;
use App\Policies\Configurations\ConfigChainPolicy;
use App\Policies\Configurations\ConfigConsortiumPolicy;
use App\Policies\Configurations\ConfigDescriptiveTypePolicy;
use App\Policies\Configurations\ConfigInsuranceDocumentationTypePolicy;
use App\Policies\Configurations\ConfigJobDescriptionPolicy;
use App\Policies\Configurations\ConfigRoomBedTypePolicy;
use App\Policies\Configurations\ConfigServiceTypePolicy;
use App\Policies\Configurations\KeyMappingOwnerPolicy;
use App\Policies\HotelPolicy;
use App\Policies\HotelRatePolicy;
use App\Policies\HotelRoomPolicy;
use App\Policies\ImageGalleryPolicy;
use App\Policies\ImagePolicy;
use App\Policies\InformationalServicePolicy;
use App\Policies\InsurancePlanPolicy;
use App\Policies\InsuranceProviderDocumentationPolicy;
use App\Policies\InsuranceProviderPolicy;
use App\Policies\InsuranceRateTierPolicy;
use App\Policies\InsuranceRestrictionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\TravelAgencyCommissionPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\HotelContentRepository\Models\Commission;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\KeyMappingOwner;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceProviderDocumentation;
use Modules\Insurance\Models\InsuranceRateTier;
use Modules\Insurance\Models\InsuranceRestriction;

class PermissionServiceProvider extends ServiceProvider
{
    private static array $permissions = [
        'statistic-charts',
        'swagger-docs',
        'log-viewer',
        'activities',
        'config-group',
    ];

    private static array $modelPolicies = [
        HotelRoom::class => HotelRoomPolicy::class,
        HotelRate::class => HotelRatePolicy::class,
        Hotel::class => HotelPolicy::class,
        Vendor::class => VendorPolicy::class,
        Product::class => ProductPolicy::class,
        InsuranceProvider::class => InsuranceProviderPolicy::class,
        InsuranceProviderDocumentation::class => InsuranceProviderDocumentationPolicy::class,
        InsuranceRestriction::class => InsuranceRestrictionPolicy::class,
        InsuranceRateTier::class => InsuranceRateTierPolicy::class,
        InsurancePlan::class => InsurancePlanPolicy::class,
        ConfigAttribute::class => ConfigAttributePolicy::class,
        ConfigAttributeCategory::class => ConfigAttributeCategoryPolicy::class,
        ConfigAmenity::class => ConfigAmenityPolicy::class,
        ConfigConsortium::class => ConfigConsortiumPolicy::class,
        ConfigDescriptiveType::class => ConfigDescriptiveTypePolicy::class,
        ConfigJobDescription::class => ConfigJobDescriptionPolicy::class,
        ConfigServiceType::class => ConfigServiceTypePolicy::class,
        ConfigChain::class => ConfigChainPolicy::class,
        ConfigInsuranceDocumentationType::class => ConfigInsuranceDocumentationTypePolicy::class,
        ConfigRoomBedType::class => ConfigRoomBedTypePolicy::class,
        KeyMappingOwner::class => KeyMappingOwnerPolicy::class,
        Commission::class => CommissionPolicy::class,
        InformationalService::class => InformationalServicePolicy::class,
        TravelAgencyCommission::class => TravelAgencyCommissionPolicy::class,
        ImageGallery::class => ImageGalleryPolicy::class,
        Image::class => ImagePolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        foreach (self::$modelPolicies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        foreach (self::$permissions as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->hasPermission($permission) || $user->hasRole(RoleSlug::ADMIN->value);
            });
        }
    }
}
