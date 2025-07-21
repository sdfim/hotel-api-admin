<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelResponseFactory
{
    public static function create(): HotelResponse
    {
        /** @var HotelResponse $hotelResponse */
        $hotelResponse = app(HotelResponse::class);

        $hotelResponse->setDistanceFromSearchLocation(0);
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
        $hotelResponse->setSupplierInformation([]);
        $hotelResponse->setHotelContacts([]);

        return $hotelResponse;
    }
}
