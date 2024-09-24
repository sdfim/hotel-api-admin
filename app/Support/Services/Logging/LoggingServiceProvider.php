<?php

namespace App\Support\Services\Logging;

use Carbon\Laravel\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use App\Support\Services\Logging\Processors\LogUuidProcessor;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        /**
         * Check if the default driver supports processors (decorator pattern)
         * if so, register all the processors
         */
        $logger = $this->app->get('log')->getLogger();

        if (method_exists($logger, 'pushProcessor')) {
            $logger->pushProcessor(new LogUuidProcessor());
        }
    }
}
