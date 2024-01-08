<?php

namespace Modules\API\PropertyWeighting;

use App\Repositories\PropertyWeightingRepository;
use Illuminate\Support\Facades\Log;

class EnrichmentWeight
{
    protected float|string $current_time = 0.0;

    public function __construct()
    {
        $this->current_time = microtime(true);
    }

    /**
     * @param array $clientResponse
     * @param string $type
     * @return array
     */
    public function enrichmentContent(array $clientResponse, string $type = ''): array
    {
        $this->executionTime();

        $weights = PropertyWeightingRepository::getWeights();
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

        Log::info('EnrichmentWeight | enrichmentContent  - ' . $this->executionTime() . 's');

        return $clientResponse;
    }

    /**
     * @param array $clientResponse
     * @param string $type
     * @return array
     */
    public function enrichmentPricing(array $clientResponse, string $type = ''): array
    {
        $this->executionTime();

        // step1 !isset supplier_id
        $s1Weights = PropertyWeightingRepository::getWeights();
        $s1WeightsProps = $s1Weights->pluck('property')->toArray();
        $s1WeightsVol = $s1Weights->pluck('weight', 'property')->toArray();

        // step2 isset supplier_id
        $s2Weights = PropertyWeightingRepository::getWeightsNot();
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

        Log::info('EnrichmentWeight | api/pricing/search - ' . $this->executionTime() . 's');

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
