<?php

namespace Modules\API\Controllers\ApiHandlers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\HotelContentRepository\Services\HotelContentApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HotelApiHandlerV1 extends HotelApiHandler
{
    public function __construct(
        private readonly HotelContentApiService $hotelContentApiService,
    ) {}

    public function search(Request $request): JsonResponse
    {
        try {
            $keyContent = $this->hotelContentApiService->generateCacheKey($request);

            if (Cache::has($keyContent.':dataResponse')) {
                $contentResults = Cache::get($keyContent.':dataResponse');
            } else {
                $contentResults = $this->hotelContentApiService->fetchContentResults($request);
                Cache::put($keyContent.':dataResponse', $contentResults, now()->addMinutes(self::TTL));
            }

            $page = $request->input('page', 1);
            $resultsPerPage = $request->input('results_per_page', 1000);
            $paginatedResults = $this->hotelContentApiService->sortAndPaginate($contentResults, $page, $resultsPerPage);

            return $this->sendResponse([
                'query' => $request->all(),
                'total_count' => count($contentResults),
                'page' => $page,
                'results_per_page' => $resultsPerPage,
                'results' => $paginatedResults,
            ], 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $keyDetail = $this->hotelContentApiService->generateCacheKey($request);

            if (Cache::has($keyDetail.':dataResponse')) {
                $detailResults = Cache::get($keyDetail.':dataResponse');
            } else {
                $giataCodes = $this->hotelContentApiService->getGiataCodes($request);
                $detailResults = $this->hotelContentApiService->fetchDetailResults($giataCodes);
                Cache::put($keyDetail.':dataResponse', $detailResults, now()->addMinutes(self::TTL));
            }

            return $this->sendResponse(['results' => $detailResults], 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }
}
