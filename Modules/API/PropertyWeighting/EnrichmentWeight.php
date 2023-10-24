<?php

namespace Modules\API\PropertyWeighting;

use App\Models\PropertyWeighting;
use App\Models\Supplier;

class EnrichmentWeight 
{
	private Supplier $supplier;
	public function __construct() {
		$this->supplier = new Supplier();
	}
	public function enrichmentContent(array $clientResponse, string $type): array
	{
		$weitghts = PropertyWeighting::where('supplier_id', null)->get();
		
		$weitghtsProps= $weitghts->pluck('property')->toArray();
		$weitghtsVol = $weitghts->pluck('weight', 'property')->toArray();

		foreach ($clientResponse as $supllierName => $supplierResponse) {
			foreach ($supplierResponse as $key => $hotelData) {
				if (in_array($hotelData['giata_hotel_code'], $weitghtsProps)) {
					$clientResponse[$supllierName][$key]['weight'] = $weitghtsVol[$hotelData['giata_hotel_code']];
				} else {
					$clientResponse[$supllierName][$key]['weight'] = 0;
				}
			}
		}

		return $clientResponse;
	}

	public function enrichmentPricing(array $clientResponse, string $type): array
	{
		# step1 !isset supplier_id
		$weitghts = PropertyWeighting::where('supplier_id', null)->get();
		
		$weitghtsProps= $weitghts->pluck('property')->toArray();
		$weitghtsVol = $weitghts->pluck('weight', 'property')->toArray();

		foreach ($clientResponse as $supllierName => $supplierResponse) {
			foreach ($supplierResponse as $key => $hotelData) {
				if (in_array($hotelData['giata_hotel_id'], $weitghtsProps)) {
					$clientResponse[$supllierName][$key]['weight'] = $weitghtsVol[$hotelData['giata_hotel_id']];
				} else {
					$clientResponse[$supllierName][$key]['weight'] = 0;
				}
			}
		}

		# step2 isset supplier_id
		$weitghts = PropertyWeighting::whereNot('supplier_id', null)->get();
		
		$weitghtsProps= $weitghts->pluck('property')->toArray();
		$weitghtsVol = $weitghts->pluck('weight', 'property')->toArray();

		foreach ($clientResponse as $supllierName => $supplierResponse) {
			foreach ($supplierResponse as $key => $hotelData) {
				if (in_array($hotelData['giata_hotel_id'], $weitghtsProps)) {
					$clientResponse[$supllierName][$key]['weight'] = $weitghtsVol[$hotelData['giata_hotel_id']];
				} 
			}
		}

		return $clientResponse;
	}
}