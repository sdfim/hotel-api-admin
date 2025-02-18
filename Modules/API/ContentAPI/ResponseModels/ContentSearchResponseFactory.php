<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentSearchResponseFactory
{
    public static function create(): ContentSearchResponse
    {
        /** @var ContentSearchResponse $contentSearchResponse */
        $contentSearchResponse = app(ContentSearchResponse::class);

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
        $contentSearchResponse->setWeight(0);
        $contentSearchResponse->setDepositInformation([]);
        $contentSearchResponse->setCancellationPolicies([]);
        $contentSearchResponse->setDrivers([]);

        return $contentSearchResponse;
    }
}
