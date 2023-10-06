<?php

namespace Modules\API\Controllers;

use Modules\API\BaseController;
use App\Models\ExpediaContent;
use App\Models\MapperExpediaGiata;
use App\Models\Suppliers;
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
use Modules\Inspector\InspectorController;

class ExpediaHotelApiHandler extends BaseController implements ApiHandlerInterface
{
	private ExperiaService $experiaService;
	private InspectorController $apiInspector;
	protected $current_time;

	public function __construct(ExperiaService $experiaService) {
		$this->experiaService = $experiaService;
		$this->apiInspector = new InspectorController();
	}
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
            $results = $searchBuilder->applyFilters($filters);

			# enricmant GIATA code
			$selectList = ['mapper_expedia_giatas.giata_id'];
			foreach ($fields as $field) {
				$selectList[] =  'expedia_contents.'.$field;
			}
			$results->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.expedia_id', '=', 'expedia_contents.property_id')
				->whereNotNull('mapper_expedia_giatas.expedia_id')
				->select($selectList);

			$results = $results->get();

			$ids = collect($results)->pluck('property_id')->toArray();

            $results = $expedia->dtoDbToResponse($results, $fields);

		} catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler | preSearchData' . $e->getMessage());
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
			$filters = $preSearchData['filters'] ?? null;
			return $this->sendResponse(['count' => count($results), 'query' => $filters,  'results' => $results], 'success');
        } catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler | search' . $e->getMessage());
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
			\Log::debug('ExpediaHotelApiHandler | price | Validator: ' . $this->executionTime() . ' seconds');

			$preSearchData = $this->preSearchData ($request);
			$filters = $preSearchData['filters'] ?? null;
			\Log::debug('ExpediaHotelApiHandler | price | preSearchData: ' . $this->executionTime() . ' seconds');

			$key = 'search:'.md5(json_encode($filters));
			if (Cache::has($key)) {
				$output = Cache::get($key);
			} else {
				# get PriceData from RapidAPI Expedia
				$priceData = $this->experiaService->getExpediaPriceByPropertyIds($preSearchData['ids'], $filters);
				# add price to response
				$output = [];
				foreach ($preSearchData['results'] as $value) {
					if (isset($priceData[$value->property_id])) {
						$prices_property = json_decode($priceData[$value->property_id]);
						if (count($prices_property)) {
							$prices_property = (array)$prices_property[0];
							$prices_property['giata_id'] = $value->giata_id;
							$output[$value->property_id] = (object) array_merge(['content' => $value], ['price' => $prices_property]);
						}
					}
				}
				Cache::put($key, $output, now()->addMinutes(120));
			}
			\Log::debug('ExpediaHotelApiHandler | price | AsyncGetPrices: ' . $this->executionTime() . ' seconds');

			# save data to Inspector
            // TODO: create Seeder to create default supplier record with Expedia
			$supplier_id = Suppliers::where('name', 'Expedia')->first()->id;
			$inspector = $this->apiInspector->save($filters, $output, $supplier_id);
			\Log::debug('ExpediaHotelApiHandler | price | save data to Inspector: ' . $this->executionTime() . ' seconds');

            return $this->sendResponse([
				'count' => count($output),
				'inspector' => $inspector ?? '',
				'query' => $filters,
				'results' => $output,
			], 'success');
        } catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
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
            \Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'falied');
        }
    }

	private function executionTime ()
    {
        $execution_time = (microtime(true) - $this->current_time);
        $this->current_time = microtime(true);

        return $execution_time;
    }

}
