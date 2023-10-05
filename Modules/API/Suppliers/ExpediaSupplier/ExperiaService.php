<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Illuminate\Support\Facades\Cache;

class ExperiaService
{
	private PropertyCallFactory $rapidCallFactory;

	public function __construct(PropertyCallFactory $rapidCallFactory) {
		$this->rapidCallFactory = $rapidCallFactory;
	}

	public function getExpediaPriceByPropertyIds (array $queryIds, array $query) :array
    {
		$start_time = microtime(true);

        $property['checkin'] = $query['checkin'] ?? date("Y-m-d");
        $property['checkout'] = $query['checkout'] ?? date('Y-m-d', strtotime(date("Y-m-d") . ' +2 days'));
        $property['occupancy'] = $query['occupancy'] ?? ["2"];
		$propertyIds = $queryIds ?? [];
		
		try {
			$propertyPriceCall = $this->rapidCallFactory->createPropertyPriceCall($property);
			\Log::debug('ExpediaHotelApiHandler | price | step1 | getExpediaPriceByPropertyIds ' );
        	$dataPrice = $propertyPriceCall->getPriceData($propertyIds);
		} catch (\Exception $e) {
			\Log::error('ExpediaHotelApiHandler | getExpediaPriceByPropertyIds' . $e->getMessage());
			return [];
		}
        
		\Log::debug('ExpediaHotelApiHandler | price | step2 | getExpediaPriceByPropertyIds ' , ['dataPrice' => $dataPrice] );

		// TODO: Save Data to Redis for Inspector
        Cache::put('dataPriceAll', json_encode($dataPrice), 3600);
        $value = Cache::get('dataPriceAll');

		// TODO: Save Data to storage for Inspector
        \Log::debug('RapidPropertyPriceCall', ['value' => json_decode($value)]);

		$end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
		\Log::debug('Import completed. ' . round($execution_time, 2) . " seconds");

		return $dataPrice;
    }

}