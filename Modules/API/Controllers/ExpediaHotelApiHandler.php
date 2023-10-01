<?php

namespace Modules\API\Controllers;

use Modules\API\BaseController;
use App\Models\ExpediaContent;
use Modules\API\Controllers\ApiHandlerInterface;
use Modules\API\Requests\SearchHotelRequest;
use Modules\API\Requests\DetailHotelRequest;
use Modules\API\Requests\PriceHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ExpediaHotelApiHandler extends BaseController implements ApiHandlerInterface
{
	/**
	 * @param Request $request
	 * @return array|null
	 */
	private function preSearchData (Request $request): array|null
    {
		try {
			$searchRequest = new SearchHotelRequest();
			$rules = $searchRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();

            $expedia = new ExpediaContent();
            $fields = $request->get('fullList') ? $expedia->getFullListFields() : $expedia->getShortListFields();
			$query = $expedia->select($fields);

            $searchBuilder = new HotelSearchBuilder($query);
            $results = $searchBuilder->applyFilters($filters)->get();

			$ids = collect($results)->pluck('property_id')->toArray();
            $results = $expedia->dtoDbToResponse($results, $fields);

		} catch (\Exception $e) {
            \Log::error('HotelApiHandler | preSearchData' . $e->getMessage());
            return null;
        }

		return ['ids' => $ids ?? 0, 'results' => $results ?? null, 'filters' => $filters ?? null];
	}

	/**
     * @param Request $request
     * @return JsonResponse
     */
    public function search (Request $request): JsonResponse
    {
		try {
			$preSearchData = $this->preSearchData ($request);
			$results = $preSearchData['results'] ?? null;
			return $this->sendResponse(['count' => count($results), 'results' => $results], 'success');
        } catch (\Exception $e) {
            \Log::error('HotelApiHandler | search' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'falied');
		}
	}

	/**
     * @param Request $request
     * @return JsonResponse
     */
    public function price (Request $request): JsonResponse
    {
		try {
			$priceRequest = new PriceHotelRequest();
			$rules = $priceRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();

			$preSearchData = $this->preSearchData ($request);
			$filters = $preSearchData['filters'] ?? null;

			$key = 'search:'.md5(json_encode($filters));
			if (Cache::has($key)) {
				$output = Cache::get($key);
			} else {
				# get PriceData from RapidAPI Expedia
				$priceData = ExperiaService::getExpediaPriceByPropertyIds($preSearchData['ids'], $filters);
				# add price to results
				$output = [];
				foreach ($preSearchData['results'] as $value) {
					if (isset($priceData[$value->property_id])) {
						$prices_property = json_decode($priceData[$value->property_id]);
						if (count($prices_property)) {
							$prices_property = (array)$prices_property[0];
							\Log::debug('search' . count($prices_property), [$value->property_id => (array)$prices_property]);
							$output[$value->property_id] = (object) array_merge(['content' => $value], ['price' => $prices_property]);
						}
					} 
				}
				Cache::put($key, $output, now()->addMinutes(120));
			}

            return $this->sendResponse(['count' => count($output), 'results' => $output], 'success');
        } catch (\Exception $e) {
            \Log::error('ExpediaController ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'falied');
        }
    }

	/**
     * @param Request $request
     * @return JsonResponse
     */
    public function detail(Request $request): JsonResponse
    {
        try {
			// $detailRequest = new DetailHotelRequest();
			// $rules = $detailRequest->rules();
			// $validator = Validator::make($request->all(), $rules)->validated();

            $expedia = new ExpediaContent();
            $property_id = $request->get('property_id') ?? null;
            $results = $expedia->where('property_id', $property_id)->get();
            $results = $expedia->dtoDbToResponse($results, $expedia->getFullListFields());

            return $this->sendResponse(['results' => $results], 'success');
        } catch (\Exception $e) {
            \Log::error('ExpediaController ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'falied');
        }
    }
}
