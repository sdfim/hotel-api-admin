<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailResponseFactory
{
    public static function create(): ContentDetailResponse
    {
        $contentDetailResponse = new ContentDetailResponse();

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

        return $contentDetailResponse;
    }
}
