<?php

namespace App\Support\Services\Logging;

use Carbon\Laravel\ServiceProvider;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use App\Support\Services\Logging\Processors\LogUuidProcessor;
use App\Support\Helpers\UniversalUniqueIdentifierHelper;
use App\Support\Services\Logging\Processors\LogIpProcessor;
use App\Support\Services\Logging\Processors\LogRequestDataProcessor;
use App\Support\Services\Logging\Processors\LogRequestUrlProcessor;
use Monolog\Processor\MemoryUsageProcessor;

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
         * The next section allow us to add the Unique ID
         * for commands to identify it in the logs
         * please avoid to remove this
         */
        if (app()->runningInConsole()) {
            try {
                request()->attributes->add(['uuid' => UniversalUniqueIdentifierHelper::uuidv4()]);
            } catch (Exception $e) {
            }
        }

        /**
         * Check if the default driver supports processors (decorator pattern)
         * if so, register all the processors
         */
        $logger = $this->app->get('log')->getLogger();

        if (method_exists($logger, 'pushProcessor')) {
            $logger->pushProcessor(new LogUuidProcessor());
            $logger->pushProcessor(new LogIpProcessor());
            $logger->pushProcessor(new LogRequestUrlProcessor());
            $logger->pushProcessor(new LogRequestDataProcessor());

            if (config('app.debug')) {
                $logger->pushProcessor(new MemoryUsageProcessor());
            }
        }
    }
}
