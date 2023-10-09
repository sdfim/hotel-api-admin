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
        $property['checkin'] = $query['checkin'] ?? date("Y-m-d");
        $property['checkout'] = $query['checkout'] ?? date('Y-m-d', strtotime(date("Y-m-d") . ' +2 days'));
        $property['occupancy'] = $query['occupancy'] ?? ["2"];
		$propertyIds = $queryIds ?? [];
		
		try {
			$propertyPriceCall = $this->rapidCallFactory->createPropertyPriceCall($property);
        	$dataPrice = $propertyPriceCall->getPriceData($propertyIds);
		} catch (\Exception $e) {
			\Log::error('ExpediaHotelApiHandler | getExpediaPriceByPropertyIds' . $e->getMessage());
			return [];
		}

		return $dataPrice;
    }

}