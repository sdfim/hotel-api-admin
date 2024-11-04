<?php

namespace App\Providers;

use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigChain;
use App\Models\Configurations\ConfigConsortium;
use App\Models\Configurations\ConfigDescriptiveType;
use App\Models\Configurations\ConfigJobDescription;
use App\Models\Configurations\ConfigServiceType;
use App\Models\InformationalService;
use App\Policies\Configurations\ConfigAttributePolicy;
use App\Policies\Configurations\ConfigChainPolicy;
use App\Policies\Configurations\ConfigConsortiumPolicy;
use App\Policies\Configurations\ConfigDescriptiveTypePolicy;
use App\Policies\Configurations\ConfigJobDescriptionPolicy;
use App\Policies\Configurations\ConfigServiceTypePolicy;
use App\Policies\HotelImagePolicy;
use App\Policies\HotelPolicy;
use App\Policies\ImageGalleryPolicy;
use App\Policies\InformationalServicePolicy;
use App\Policies\InsuranceProviderPolicy;
use App\Policies\InsuranceRestrictionPolicy;
use App\Policies\TravelAgencyCommissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelImage;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRestriction;

class PermissionServiceProvider extends ServiceProvider
{
    private static array $permissions = [
        'statistic-charts',
        'swagger-docs',
        'log-viewer',
        'config-group',
    ];

    private static array $modelPolicies = [
        Hotel::class                   => HotelPolicy::class,
        InsuranceProvider::class       => InsuranceProviderPolicy::class,
        InsuranceRestriction::class    => InsuranceRestrictionPolicy::class,
        ConfigAttribute::class         => ConfigAttributePolicy::class,
        ConfigConsortium::class        => ConfigConsortiumPolicy::class,
        ConfigDescriptiveType::class   => ConfigDescriptiveTypePolicy::class,
        ConfigJobDescription::class    => ConfigJobDescriptionPolicy::class,
        ConfigServiceType::class       => ConfigServiceTypePolicy::class,
        ConfigChain::class             => ConfigChainPolicy::class,
        InformationalService::class    => InformationalServicePolicy::class,
        TravelAgencyCommission::class  => TravelAgencyCommissionPolicy::class,
        ImageGallery::class            => ImageGalleryPolicy::class,
        HotelImage::class              => HotelImagePolicy::class,
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
                return $user->hasPermission($permission) || $user->hasRole('admin');
            });
        }
    }
}
