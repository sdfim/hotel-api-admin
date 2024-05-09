<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentSearchResponseFactory
{
    public static function create(): ContentSearchResponse
    {
        $contentSearchResponse = new ContentSearchResponse();

        $contentSearchResponse->setGiataHotelCode(0);
        $contentSearchResponse->setImages([]);
        $contentSearchResponse->setDescription([]);
        $contentSearchResponse->setHotelName('');
        $contentSearchResponse->setDistance('');
        $contentSearchResponse->setLatitude('');
        $contentSearchResponse->setLongitude('');
        $contentSearchResponse->setRating('');
        $contentSearchResponse->setAmenities([]);
        $contentSearchResponse->setGiataDestination('');
        $contentSearchResponse->setUserRating('');
        $contentSearchResponse->setImportantInformation([]);
        $contentSearchResponse->setSupplierInformation([]);

        return $contentSearchResponse;
    }
}
