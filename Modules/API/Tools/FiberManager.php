<?php

namespace Modules\API\Tools;

use Fiber;
use GuzzleHttp\Promise\Utils;

class FiberManager
{
    private array $fibers = [];

    private array $awaitMap = [];

    private array $promises = [];

    private array $resolvedResponses = [];

    /**
     * Adds a new fiber to the manager with an optional await flag.
     *
     * This method registers a fiber with a unique key and a callback function.
     * The `$await` parameter determines whether the fiber's result should be awaited.
     *
     * @param string $key The unique key to identify the fiber.
     * @param  callable  $callback  The callback function to execute within the fiber.
     * @param  bool  $await  Whether to await the fiber's result (default: true).
     */
    public function add(string $key, callable $callback, bool $await = true): void
    {
        $this->fibers[$key] = new Fiber($callback);
        $this->awaitMap[$key] = $await;
    }

    /**
     * Starts all fibers and collects their promises for asynchronous execution.
     *
     * This method iterates through all registered fibers, starts them if they are not already started,
     * and collects their results as promises. Promises are stored in an array for later resolution.
     * Non-awaitable fibers (e.g., MySQL queries) are skipped.
     */
    public function startAll(): void
    {
        $promises = [];
        foreach ($this->fibers as $key => $fiber) {
            if (! $fiber->isStarted()) {
                $result = $fiber->start();

                // MySql query don't need to be awaited
                if (! ($this->awaitMap[$key] ?? true)) {
                    continue; // skip await
                }

                // Only GuzzleHttp\Promise\PromiseInterface can be awaited and add to promises
                if (! $result) {
                    continue;
                }
                foreach ($result as $chunkKey => $promise) {
                    $promises["{$key}_{$chunkKey}"] = $promise;
                }
            }
        }

        $this->promises = $promises;
    }

    /**
     * Waits for all promises to resolve and processes their results.
     *
     * This method executes all stored promises in parallel, waits for them to complete,
     * and stores their resolved results in the `resolvedResponses` property.
     */
    public function wait(): void
    {
        $this->resolvedResponses = [];

        // Run all the promises in parallel
        $results = Utils::all($this->promises)->wait();

        foreach ($results as $key => $result) {
            $this->resolvedResponses[$key] = $result;
        }
    }

    /**
     * Returns the resolved responses grouped by supplier name and query package.
     *
     * This method processes the resolved responses stored in the `resolvedResponses` property
     * and organizes them into a structured format. The responses are grouped by supplier name
     * and query package for easier access and analysis.
     *
     * @return array The structured array of grouped resolved responses.
     */
    public function getResume(): array
    {
        return $this->processResolvedResponses($this->resolvedResponses);
    }

    public function getFibers(): array
    {
        return $this->fibers;
    }

    /**
     * Processes the resolved responses and groups them by supplier name and query package.
     *
     * This method takes an array of resolved responses, extracts the supplier name,
     * query package, and chunk key from the response keys, and organizes the data
     * into a structured format for easier access and analysis.
     *
     * @param  array  $resolvedResponses  The array of resolved responses to process.
     * @return array The structured array grouped by supplier name and query package.
     */
    public function processResolvedResponses(array $resolvedResponses): array
    {
        $resume = [];
        foreach ($resolvedResponses as $key => $resolvedResponse) {
            [$supplierName, $queryPackage, $chunkKey] = explode('_', $key);
            $resume[$supplierName][$queryPackage][] = $resolvedResponse;
        }

        return $resume;
    }
}
