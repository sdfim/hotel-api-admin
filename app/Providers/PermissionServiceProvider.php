<?php

namespace App\Providers;

use App\Policies\HotelPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\HotelContentRepository\Models\Hotel;

class PermissionServiceProvider extends ServiceProvider
{
    private static array $permissions = [
        'statistic-charts',
        'swagger-docs',
        'log-viewer',
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
        Gate::policy(Hotel::class, HotelPolicy::class);

        foreach (self::$permissions as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->hasPermission($permission) || $user->hasRole('admin');
            });
        }
    }
}
