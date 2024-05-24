<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Exception;
use Illuminate\Support\Facades\Log;

class ExpediaService
{
    /**
     * @var RapidClient
     */
    private RapidClient $rapidClient;

    /**
     */
    public function __construct()
    {
        $this->rapidClient = new RapidClient();
    }

    /**
     * @param array $propertyIds
     * @param array $query
     * @return array
     * @throws \Throwable
     */
    public function getExpediaPriceByPropertyIds(array $propertyIds, array $query, array $searchInspector): array
    {
        try {
            $propertyPriceCall = new PropertyPriceCall($this->rapidClient, $query);
            $dataPrice = $propertyPriceCall->getPriceData($propertyIds, $searchInspector);
        } catch (Exception $e) {
            Log::error('ExpediaService | getExpediaPriceByPropertyIds' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return [];
        }

        return $dataPrice;
    }
}
