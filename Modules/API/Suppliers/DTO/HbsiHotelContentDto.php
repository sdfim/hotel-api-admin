<?php

namespace Modules\API\Suppliers\DTO;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;

class HbsiHotelContentDto
{
    /**
     * @param array $supplierResponse
     * @return array
     */
    public function HbsiToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $hotelResponse = new ContentSearchResponse();
            $hotelResponse->setGiataHotelCode(isset($hotel['giata_id']) ? intval($hotel['giata_id']) : 0);
            $hotelResponse->setImages($hotel['images'] ?? []);
            $hotelResponse->setDescription($hotel['description'] ?? '');
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setDistance($hotel['distance'] ?? '');
            $hotelResponse->setLatitude($hotel['address']['latitude'] ?? '');
            $hotelResponse->setLongitude($hotel['address']['longitude'] ?? '');
            $hotelResponse->setRating($hotel['rating'] ?? '');
            $hotelResponse->setAmenities($hotel['amenities'] ?? []);
            $hotelResponse->setGiataDestination($hotel['address']['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = array_merge($hotelResponse->toArray(), ['perc' => $hotel['perc'] ?? 0]);
        }

        return $contentSearchResponse;
    }
}
