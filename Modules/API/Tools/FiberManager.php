<?php

namespace Modules\API\Tools;

use Fiber;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;

class FiberManager
{
    private array $fibers = [];

    private array $promises = [];

    private array $resolvedResponses = [];

    private $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function add(string $key, callable $callback): void
    {
        $this->fibers[$key] = new Fiber($callback);
    }

    public function startAll(): void
    {
        $promises = [];
        foreach ($this->fibers as $key => $fiber) {
            if (! $fiber->isStarted()) {
                $startTime = microtime(true);
                $result = $fiber->start();

                if (is_array($result)) {
                    foreach ($result as $chunkKey => $promise) {
                        $promises["{$key}_{$chunkKey}"] = $promise;
                    }
                } else {
                    $promises[$key] = $result;
                }

                Log::info("FiberManager _ Fiber $key execution time: ".(microtime(true) - $startTime).' seconds');
            }
        }

        $this->promises = $promises;
    }

    public function wait(): void
    {
        $this->resolvedResponses = [];

        foreach ($this->promises as $key => $promiseOrCallback) {
            $startTime = microtime(true);

            if ($promiseOrCallback instanceof \React\Promise\PromiseInterface) {
                $promiseOrCallback->then(
                    function ($value) use ($key, $startTime) {
                        $this->resolvedResponses[$key] = $value;
                        Log::info("FiberManager _ Promise $key execution time: ".(microtime(true) - $startTime).' seconds');
                    },
                    function ($reason) use ($key) {
                        Log::error("FiberManager _ Promise $key rejected with reason: ".$reason);
                    }
                );
            } elseif ($promiseOrCallback instanceof \GuzzleHttp\Promise\PromiseInterface) {
                // Handle Guzzle promise (asynchronous)
                $this->resolvedResponses[$key] = $promiseOrCallback->wait();
                Log::info("FiberManager _ Promise $key execution time: ".(microtime(true) - $startTime).' seconds');
            }
        }
    }

    public function getResume(): array
    {
        return $this->processResolvedResponses($this->resolvedResponses);
    }

    public function resume(string $key, mixed $value = null): void
    {
        if (isset($this->fibers[$key]) && $this->fibers[$key]->isSuspended()) {
            $this->fibers[$key]->resume($value);
        }
    }

    public function getFibers(): array
    {
        return $this->fibers;
    }

    public function processResolvedResponses(array $resolvedResponses): array
    {
        $resume = [];
        foreach ($resolvedResponses as $key => $resolvedResponse) {
            if ($key === 'transformer') {
                $resume['transformer'] = $resolvedResponse;

                continue;
            }

            $arrKey = explode('_', $key);
            $supplierName = $arrKey[0];
            $queryPackage = $arrKey[1];

            if (count($arrKey) === 3) {
                $resume[$supplierName][$queryPackage][] = $resolvedResponse;
            } else {
                $resume[$supplierName][$queryPackage] = $resolvedResponse;
            }
        }

        return $resume;
    }
}
