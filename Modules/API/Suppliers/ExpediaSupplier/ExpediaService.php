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
        $property['checkin'] = $query['checkin'] ?? date("Y-m-d");
        $property['checkout'] = $query['checkout'] ?? date('Y-m-d', strtotime(date("Y-m-d") . ' +2 days'));
        $property['occupancy'] = $query['occupancy'] ?? ["2"];
        if (isset($query['travel_purpose'])) {
            $property['travel_purpose'] = $query['travel_purpose'];
        }

        try {
            $propertyPriceCall = $this->rapidCallFactory->createPropertyPriceCall($property);
            $dataPrice = $propertyPriceCall->getPriceData($propertyIds);
        } catch (Exception $e) {
            \Log::error('ExpediaService | getExpediaPriceByPropertyIds' . $e->getMessage());
            return [];
        }

        return $dataPrice;
    }
}
