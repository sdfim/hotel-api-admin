<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Exception;

class ExpediaService
{
    /**
     * @var PropertyPriceCall
     */
    private $propertyPriceCall;

    /**
     * @var RapidClient
     */
    private $rapidClient;

    /**
     * @param PropertyCallFactory $rapidCallFactory
     */
    public function __construct()
    {
        $this->rapidClient = new RapidClient();
    }

    /**
     * @param array $queryIds
     * @param array $query
     * @return array
     */
    public function getExpediaPriceByPropertyIds(array $propertyIds, array $query): array
    {
        try {
            $this->propertyPriceCall = new PropertyPriceCall($this->rapidClient, $query);
            $dataPrice = $this->propertyPriceCall->getPriceData($propertyIds);
        } catch (Exception $e) {
            \Log::error('ExpediaService | getExpediaPriceByPropertyIds' . $e->getMessage());
            return [];
        }

        return $dataPrice;
    }
}
