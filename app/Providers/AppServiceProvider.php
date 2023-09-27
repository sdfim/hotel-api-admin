<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register (): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot (): void
    {
        $currentUrl = \Illuminate\Support\Facades\URL::current();
        if (!str_contains($currentUrl, 'localhost') && !str_contains($currentUrl, '127.0.0.1')) {
            \URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
    }
}
