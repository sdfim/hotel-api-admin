<?php

namespace Modules\API\Suppliers\DTO\IcePortal;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\DTO\SupplierContentDtoInterface;

class IcePortalHotelContentDto implements SupplierContentDtoInterface
{
    /**
     * @param array $supplierResponse
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $hotelResponse = ContentSearchResponseFactory::create();

            $hotelResponse->setGiataHotelCode(isset($hotel['giata_id']) ? intval($hotel['giata_id']) : 0);
            $hotelResponse->setImages($hotel['images'] ?? []);
            $hotelResponse->setDescription(isset($hotel['descriptions']) ? json_decode($hotel['descriptions'], true) : []);
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setDistance($hotel['distance'] ?? '');
            $hotelResponse->setLatitude($hotel['address']['latitude'] ?? $hotel['latitude'] ?? '');
            $hotelResponse->setLongitude($hotel['address']['longitude'] ?? $hotel['latitude'] ?? '');
            $hotelResponse->setRating($hotel['rating'] ?? '');
            $hotelResponse->setAmenities($hotel['amenities'] ?? []);
            $hotelResponse->setGiataDestination($hotel['address']['city'] ?? $hotel['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = array_merge($hotelResponse->toArray(), ['perc' => $hotel['perc'] ?? 0]);
        }

        return $contentSearchResponse;
    }
}
