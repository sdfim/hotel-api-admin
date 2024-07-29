<?php

namespace Modules\API\PricingAPI\ResponseModels;

class RoomGroupsResponseFactory
{
    public static function create(): RoomGroupsResponse
    {
        $roomGroupsResponse = new RoomGroupsResponse();

        $roomGroupsResponse->setTotalPrice(0.0);
        $roomGroupsResponse->setTotalTax(0.0);
        $roomGroupsResponse->setTotalFees(0.0);
        $roomGroupsResponse->setTotalNet(0.0);
        $roomGroupsResponse->setMarkup(0.0);
        $roomGroupsResponse->setCurrency('');
        $roomGroupsResponse->setPayNow(false);
        $roomGroupsResponse->setPayAtHotel(false);
        $roomGroupsResponse->setNonRefundable(false);
        $roomGroupsResponse->setMealPlan('');
        $roomGroupsResponse->setRateId(0);
        $roomGroupsResponse->setRateDescription('');
        $roomGroupsResponse->setCancellationPolicies([]);
        $roomGroupsResponse->setOpaque(false);
        $roomGroupsResponse->setRooms([]);

        return $roomGroupsResponse;
    }
}
