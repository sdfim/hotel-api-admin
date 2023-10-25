<?php

namespace Modules\API\PropertyWeighting;

use App\Models\PropertyWeighting;

class EnrichmentWeight
{
    /**
     * @var float|string
     */
    protected float $current_time;

	public function __construct()
	{
		$this->current_time = microtime(true);
	}

    /**
     * @param array $clientResponse
     * @param string $type
     * @return array
     */
    public function enrichmentContent(array $clientResponse, string $type): array
    {
        $this->executionTime();

        $weights = PropertyWeighting::where('supplier_id', null)->get();
        $weightsProps = $weights->pluck('property')->toArray();
        $weightsVol = $weights->pluck('weight', 'property')->toArray();

        foreach ($clientResponse as $supplierName => $supplierResponse) {
            foreach ($supplierResponse as $key => $hotelData) {
                if (in_array($hotelData['giata_hotel_code'], $weightsProps)) {
                    $clientResponse[$supplierName][$key]['weight'] = $weightsVol[$hotelData['giata_hotel_code']];
                } else {
                    $clientResponse[$supplierName][$key]['weight'] = 0;
                }
            }
        }

        \Log::info('EnrichmentWeight | enrichmentContent  - ' . $this->executionTime() . 's');

        return $clientResponse;
    }

    /**
     * @param array $clientResponse
     * @param string $type
     * @return array
     */
    public function enrichmentPricing(array $clientResponse, string $type): array
    {
        $this->executionTime();

        # step1 !isset supplier_id
        $s1Weights = PropertyWeighting::where('supplier_id', null)->get();
        $s1WeightsProps = $s1Weights->pluck('property')->toArray();
        $s1WeightsVol = $s1Weights->pluck('weight', 'property')->toArray();

        # step2 isset supplier_id
        $s2Weights = PropertyWeighting::whereNot('supplier_id', null)->get();
        $s2WeightsProps = $s2Weights->pluck('property')->toArray();
        $s2WeightsVol = $s2Weights->pluck('weight', 'property')->toArray();

        foreach ($clientResponse as $supplierName => $supplierResponse) {
            foreach ($supplierResponse as $key => $hotelData) {
                if (in_array($hotelData['giata_hotel_id'], $s1WeightsProps)) {
                    $clientResponse[$supplierName][$key]['weight'] = $s1WeightsVol[$hotelData['giata_hotel_id']];
                } else {
                    $clientResponse[$supplierName][$key]['weight'] = 0;
                }
                if (in_array($hotelData['giata_hotel_id'], $s2WeightsProps)) {
                    $clientResponse[$supplierName][$key]['weight'] = $s2WeightsVol[$hotelData['giata_hotel_id']];
                }
            }
        }

        \Log::info('EnrichmentWeight | enrichmentPricing - ' . $this->executionTime() . 's');

        return $clientResponse;
    }

    /**
     * @return float
     */
    private function executionTime(): float
    {
        $execution_time = round((microtime(true) - $this->current_time), 3);
        $this->current_time = microtime(true);

        return $execution_time;
    }
}
