<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Exception;
use Illuminate\Support\Facades\Log;

class ExpediaService
{
    /**
     * @var PropertyPriceCall
     */
    private PropertyPriceCall $propertyPriceCall;

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
     */
    public function getExpediaPriceByPropertyIds(array $propertyIds, array $query): array
    {
        try {
            $this->propertyPriceCall = new PropertyPriceCall($this->rapidClient, $query);
            $dataPrice = $this->propertyPriceCall->getPriceData($propertyIds);
        } catch (Exception $e) {
            Log::error('ExpediaService | getExpediaPriceByPropertyIds' . $e->getMessage());
            return [];
        }

        return $dataPrice;
    }
}
