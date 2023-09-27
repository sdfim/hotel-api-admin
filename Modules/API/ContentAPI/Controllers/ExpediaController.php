<?php
declare(strict_types=1);

namespace Modules\API\ContentAPI\Controllers;

use Modules\API\BaseController;
use Modules\API\ContentAPI\ExpediaSupplier\Requests\SearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Illuminate\Support\Facades\DB;

class ExpediaController extends BaseController
{
	private $fullListFields = [
		'property_id', 'name', 'address', 'ratings', 'location', 
		'category', 'business_model', 'checkin', 'checkout', 
		'fees', 'policies', 'attributes', 'amenities', 
		'onsite_payments', 'rates', 
		'images',  'rooms', 
		'dates', 'descriptions', 'themes', 'chain', 'brand', 
		'statistics', 'vacation_rental_details', 'airports', 
		'spoken_languages', 'all_inclusive', 'rooms_occupancy', 
		'total_occupancy', 'city', 'rating'
	];
	private $shortListFields = [
		'property_id', 'name', 'address', 'ratings', 'location', 
		'category', 'business_model',
		'fees', 'policies', 'attributes', 'amenities', 
		'onsite_payments', 
		// 'rates', 
		'statistics', 'vacation_rental_details', 'airports', 
		'total_occupancy', 'city', 'rating', 'rooms_occupancy', 
	];
    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $filters = $request->validatedDate();
		try {

			$query = DB::connection(env(('DB_CONNECTION_2'), 'mysql2'))->table('expedia_contents'); 
			
			$fields = $request->get('fullList') ? $this->fullListFields : $this->shortListFields;

			$query = $query->select($fields);

			$searchBuilder = new HotelSearchBuilder($query);
			$results = $searchBuilder->applyFilters($filters)->get();

			$results = $this->dtoDbToResponse($results, $fields);

			return $this->sendResponse(['count' => count($results), 'results' => $results], 'success');
		} catch (\Exception $e) {
			\Log::error('ExpediaController ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

    }

	/**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function property(Request $request): JsonResponse
    {
		try {

			$query = DB::connection(env(('DB_CONNECTION_2'), 'mysql2'))->table('expedia_contents'); 

			$property_id = $request->get('property_id') ?? null;
			$results = $query->where('property_id', $property_id)->get();

			$results = $this->dtoDbToResponse($results, $this->fullListFields );

			return $this->sendResponse(['count' => count($results), 'results' => $results], 'success');
		} catch (\Exception $e) {
			\Log::error('ExpediaController ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}
    }

	private function dtoDbToResponse($results, $fields)
	{
		return collect($results)->map(function ($item) use ($fields){
			foreach ($fields as $key) {
				if (!is_string($item->$key)) continue;
				if (str_contains($item->$key, '{')) {
					$item->$key = json_decode($item->$key);
				}
			}
			return $item;
		});
	}
}
