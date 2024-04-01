<?php

namespace Modules\API\PricingAPI\ResponseModels;

class RoomResponseFactory
{
    public static function create(): RoomResponse
    {
        $roomResponse = new RoomResponse();

        $roomResponse->setGiataRoomCode('');
        $roomResponse->setGiataRoomName('');
        $roomResponse->setSupplierRoomName('');
        $roomResponse->setPerDayRateBreakdown('');
        $roomResponse->setSupplierRoomCode('');
        $roomResponse->setRoomType('');
        $roomResponse->setRateId('');
        $roomResponse->setRatePlanCode('');
        $roomResponse->setRateDescription('');
        $roomResponse->setTotalPrice(0.0);
        $roomResponse->setTotalTax(0.0);
        $roomResponse->setTotalFees(0.0);
        $roomResponse->setTotalNet(0.0);
        $roomResponse->setAffiliateServiceCharge(0.0);
        $roomResponse->setCurrency('');
        $roomResponse->setBookingItem('');
        $roomResponse->setCancellationPolicies([]);
        $roomResponse->setNonRefundable(false);
        $roomResponse->setMealPlans('');
        $roomResponse->setBedConfigurations([]);

        return $roomResponse;
    }
}
