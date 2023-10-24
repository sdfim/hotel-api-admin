<?php

namespace Modules\API\Controllers;

use App\Models\ExpediaContent;
use App\Models\GiataProperty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Illuminate\Support\Facades\Cache;

class ExpediaHotelApiHandler
{
	private const RESULT_PER_PAGE = 1000;
	private const PAGE = 1;
	private const RATING = 4;
	

	private ExpediaService $experiaService;

	protected $current_time;

	public function __construct(ExpediaService $experiaService) {
		$this->experiaService = $experiaService;
	}
	/**
	 * @param Request $request
	 * @return array|null
	 */
	private function preSearchData (Request $request, array $filters): array|null
    {
		$resultsPerPage = $request->get('results_per_page') ?? self::RESULT_PER_PAGE;
		$page = $request->get('page') ?? self::PAGE;
		$rating = $request->get('rating') ?? self::RATING;

		try {
            $expedia = new ExpediaContent();
			$filters['ids'] = $expedia->getIdsByDestinationGiata($filters['destination']);

			$fields = $request->get('fullList') ? $expedia->getFullListFields() : $expedia->getShortListFields();
			$query = $expedia->select($fields);

            $searchBuilder = new HotelSearchBuilder($query);
            $results = $searchBuilder->applyFilters($filters);

			# enrichment GIATA code
			$selectList = ['mapper_expedia_giatas.giata_id'];
			foreach ($fields as $field) {
				$selectList[] =  'expedia_contents.'.$field;
			}
			$results->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.expedia_id', '=', 'expedia_contents.property_id')
				->whereNotNull('mapper_expedia_giatas.expedia_id')
				->where('expedia_contents.rating', '>=', $rating)
				->select($selectList);

			$count = $results->count('expedia_id');

			$results = $results->offset($resultsPerPage * ($page - 1))
				->limit($resultsPerPage)
				->cursor();

			$ids = collect($results)->pluck('property_id')->toArray();

            $results = $expedia->dtoDbToResponse($results, $fields);


		} catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler | preSearchData' . $e->getMessage());
            return null;
        }

		return ['ids' => $ids ?? 0, 'results' => $results ?? null, 'filters' => $filters ?? null, 'count' => $count ?? 0];
	}

	/**
     * @param Request $request
     * @return array|null
     */
    public function search (Request $request, array $filters): array
    {
		$preSearchData = $this->preSearchData ($request, $filters);
		$results = $preSearchData['results']->toArray() ?? [];

		return ['results' => $results, 'count' => $preSearchData['count']];
	}

	/**
     * @param Request $request
     * @return JsonResponse
     */
    public function price (Request $request, array $filters): array|null
    {
		try {
			$preSearchData = $this->preSearchData ($request, $filters);
			$filters = $preSearchData['filters'] ?? null;

			\Log::debug('ExpediaHotelApiHandler ', ['results' => $preSearchData['results']]);

			// $key = 'search:'.md5(json_encode($filters));
			// if (Cache::has($key)) {
			// 	$output = Cache::get($key);
			// } else {
				# get PriceData from RapidAPI Expedia
				$priceData = $this->experiaService->getExpediaPriceByPropertyIds($preSearchData['ids'], $filters);
				# add price to response
				$output = [];
				foreach ($preSearchData['results'] as $value) {
					if (isset($priceData[$value->property_id])) {
						$prices_property = json_decode($priceData[$value->property_id]);
						if (count($prices_property)) {
							$prices_property = (array)$prices_property[0];
							// $output[$value->property_id] = (object) array_merge(['content' => $value], ['price' => ['giata_id' => $value->giata_id] + $prices_property);
							$output[$value->giata_id] = ['giata_id' => $value->giata_id] + $prices_property;
						}
					}
				}
			// 	Cache::put($key, $output, now()->addMinutes(120));
			// }

			return $output ?? null;

        } catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
            return null;
        }
    }

	/**
     * @param Request $request
     * @return object
     */
    public function detail(Request $request): object
    {
        $expedia = new ExpediaContent();

		$expedia_id = $expedia->getExpediaIdByGiataId($request->get('property_id'));

        // $expedia_id = $request->get('property_id') ?? null;

        $results = $expedia->where('property_id', $expedia_id)->get();

        return $expedia->dtoDbToResponse($results, $expedia->getFullListFields());
    }

}
