<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelCheckQuoteResponseFactory
{
    public static function create(): HotelCheckQuoteResponseModel
    {
        $model = new HotelCheckQuoteResponseModel();
        $model->setComparisonOfAmounts([]);
        $model->setCheckQuoteSearchId(null);
        $model->setHotelImage(null);
        $model->setAttributes([]);
        $model->setEmailVerification(null);
        $model->setCheckQuoteSearchQuery([]);
        $model->setGiataId(null);
        $model->setBookingItem(null);
        $model->setBookingId(null);
        $model->setCurrentSearch([]);
        $model->setFirstSearch([]);
        return $model;
    }
}

