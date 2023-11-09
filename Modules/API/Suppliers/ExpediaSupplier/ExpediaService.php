<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Exception;

class ExpediaService
{
    /**
     * @var PropertyCallFactory
     */
    private PropertyCallFactory $rapidCallFactory;

    /**
     * @param PropertyCallFactory $rapidCallFactory
     */
    public function __construct(PropertyCallFactory $rapidCallFactory)
    {
        $this->rapidCallFactory = $rapidCallFactory;
    }

    /**
     * @param array $queryIds
     * @param array $query
     * @return array
     */
    public function getExpediaPriceByPropertyIds(array $propertyIds, array $query): array
    {

        try {
            $propertyPriceCall = $this->rapidCallFactory->createPropertyPriceCall($query);
            $dataPrice = $propertyPriceCall->getPriceData($propertyIds);
        } catch (Exception $e) {
            \Log::error('ExpediaService | getExpediaPriceByPropertyIds' . $e->getMessage());
            return [];
        }

        return $dataPrice;
    }
}
