<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\API\Suppliers\ExpediaSupplier\PropertyPriceCall;
use Illuminate\Support\Facades\Cache;


class ExperiaService
{

	public static function getExpediaPriceByPropertyIds (array $queryIds, array $query) :array
    {
		$start_time = microtime(true);
		
        $apiKey = env('EXPEDIA_RAPID_API_KEY');
        $sharedSecret = env('EXPEDIA_RAPID_SHARED_SECRET');

        $client = new RapidClient($apiKey, $sharedSecret);
        $property['checkin'] = $query['checkin'] ?? date("Y-m-d");
        $property['checkout'] = $query['checkout'] ?? date('Y-m-d', strtotime(date("Y-m-d") . ' +2 days'));
        $property['occupancy'] = $query['occupancy'] ?? ["2"];
		$propertyIds = $queryIds ?? [];

        $propertyContentCall = new PropertyPriceCall($client, $property);
        $dataPrice = $propertyContentCall->getPriceData($propertyIds);

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