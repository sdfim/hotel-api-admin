<?php

namespace Modules\API\PropertyWeighting;

use App\Models\PropertyWeighting;
use App\Models\Supplier;

class EnrichmentWeight 
{
	protected $current_time;

	public function enrichmentContent(array $clientResponse, string $type): array
	{
		$this->executionTime();

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

		\Log::info('EnrichmentWeight::enrichmentContent() - ' . $this->executionTime() . 's');

		return $clientResponse;
	}

	public function enrichmentPricing(array $clientResponse, string $type): array
	{
		$this->executionTime();

		# step1 !isset supplier_id
		$s1weitghts = PropertyWeighting::where('supplier_id', null)->get();
		$s1weitghtsProps= $s1weitghts->pluck('property')->toArray();
		$s1weitghtsVol = $s1weitghts->pluck('weight', 'property')->toArray();

		# step2 isset supplier_id
		$s2weitghts = PropertyWeighting::whereNot('supplier_id', null)->get();
		$s2weitghtsProps= $s2weitghts->pluck('property')->toArray();
		$s2weitghtsVol = $s2weitghts->pluck('weight', 'property')->toArray();

		foreach ($clientResponse as $supllierName => $supplierResponse) {
			foreach ($supplierResponse as $key => $hotelData) {
				if (in_array($hotelData['giata_hotel_id'], $s1weitghtsProps)) {
					$clientResponse[$supllierName][$key]['weight'] = $s1weitghtsVol[$hotelData['giata_hotel_id']];
				} else {
					$clientResponse[$supllierName][$key]['weight'] = 0;
				}
				if (in_array($hotelData['giata_hotel_id'], $s2weitghtsProps)) {
					$clientResponse[$supllierName][$key]['weight'] = $s2weitghtsVol[$hotelData['giata_hotel_id']];
				} 
			}
		}

		\Log::info('EnrichmentWeight::enrichmentPricing() - ' . $this->executionTime() . 's');

		return $clientResponse;
	}

	private function executionTime () : float
    {
        $execution_time = round((microtime(true) - $this->current_time), 3);
        $this->current_time = microtime(true);

        return $execution_time;
    }
}