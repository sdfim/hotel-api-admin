<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelResponseFactory
{
    /**
     * @return HotelResponse
     */
    public static function create(): HotelResponse
    {
        $hotelResponse = new HotelResponse();

        $hotelResponse->setGiataHotelId(0);
        $hotelResponse->setRating('1');
        $hotelResponse->setHotelName('');
        $hotelResponse->setBoardBasis('');
        $hotelResponse->setSupplier('');
        $hotelResponse->setSupplierHotelId(0);
        $hotelResponse->setDestination('');
        $hotelResponse->setMealPlansAvailable('');
        $hotelResponse->setLowestPricedRoomGroup('');
        $hotelResponse->setPayAtHotelAvailable('');
        $hotelResponse->setPayNowAvailable('');
        $hotelResponse->setNonRefundableRates('');
        $hotelResponse->setRefundableRates('');
        $hotelResponse->setRoomGroups([]);
        $hotelResponse->setRoomCombinations([]);

        return $hotelResponse;
    }
}
