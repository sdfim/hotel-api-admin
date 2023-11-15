<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use App\Http\CustomUrlGenerator;

class CustomUrlServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
	public function register()
    {
        $this->app->bind('url', function ($app) {
            return new CustomUrlGenerator($app['router']->getRoutes(), $app->rebinding(
                'request', $this->requestRebinder()
            ));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the request rebinder for the provider.
     *
     * @return \Closure
     */
    protected function requestRebinder()
    {
        return function ($app, $request) {
			$app['url']->setRequest($request);
        };
    }
}
