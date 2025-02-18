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
//        $contentDetailResponse->setDescription('');
        $contentDetailResponse->setHotelName('');
        $contentDetailResponse->setDistance('');
        $contentDetailResponse->setLatitude('');
        $contentDetailResponse->setLongitude('');
        $contentDetailResponse->setRating('');
        $contentDetailResponse->setAmenities([]);
        $contentDetailResponse->setGiataDestination('');
        $contentDetailResponse->setUserRating('');
        $contentDetailResponse->setSpecialInstructions([]);
        $contentDetailResponse->setCheckInTime('');
        $contentDetailResponse->setCheckOutTime('');
        $contentDetailResponse->setHotelFees([]);
        $contentDetailResponse->setPolicies([]);
        $contentDetailResponse->setDescriptions([]);
        $contentDetailResponse->setAddress('');
        $contentDetailResponse->setSupplierInformation([]);
        $contentDetailResponse->setDepositInformation([]);
        $contentDetailResponse->setCancellationPolicies([]);
        $contentDetailResponse->setDrivers([]);

        return $contentDetailResponse;
    }
}
