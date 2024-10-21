<?php

namespace App\Providers;

use App\Policies\HotelPolicy;
use App\Policies\InsuranceProviderPolicy;
use App\Policies\InsuranceRestrictionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRestriction;

class PermissionServiceProvider extends ServiceProvider
{
    private static array $permissions = [
        'statistic-charts',
        'swagger-docs',
        'log-viewer',
    ];

    private static array $modelPolicies = [
        Hotel::class                => HotelPolicy::class,
        InsuranceProvider::class    => InsuranceProviderPolicy::class,
        InsuranceRestriction::class => InsuranceRestrictionPolicy::class,
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
