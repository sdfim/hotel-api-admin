<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailResponseFactory
{
    public static function create(): ContentDetailResponse
    {
        /** @var ContentDetailResponse $contentDetailResponse */
        $contentDetailResponse = app(ContentDetailResponse::class);

        $contentDetailResponse->setGiataHotelCode(0);
        $contentDetailResponse->setImages([]);
        $contentDetailResponse->setHotelName('');
        $contentDetailResponse->setLatitude('');
        $contentDetailResponse->setLongitude('');
        $contentDetailResponse->setRating('');
        $contentDetailResponse->setNearestAirports([]);
        $contentDetailResponse->setAmenities([]);
        $contentDetailResponse->setUltimateAmenities([]);
        $contentDetailResponse->setGiataDestination('');
        $contentDetailResponse->setUserRating('');
        $contentDetailResponse->setDescriptions([]);
        $contentDetailResponse->setAddress('');
        $contentDetailResponse->setDepositInformation([]);
        $contentDetailResponse->setCancellationPolicies([]);
        $contentDetailResponse->setDrivers([]);

        return $contentDetailResponse;
    }
}
