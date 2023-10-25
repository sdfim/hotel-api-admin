<?php

namespace Modules\API\Suppliers\DTO;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;

class ExpediaContentDto
{
    /**
     * @param array $supplierResponse
     * @return array
     */
    public function ExpediaToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];
        foreach ($supplierResponse as $hotel) {
            $amenities = $hotel['amenities'] ?? [];
            $hotelResponse = new ContentSearchResponse();

            $images = [];
            $countImages = 0;
            foreach ($hotel['images'] as $image) {
                if ($countImages == 5) break;
                $images[] = $image['links']['350px']['href'];
                $countImages++;
            }

            $hotelResponse->setGiataHotelCode($hotel['giata_id']);
            $hotelResponse->setImages($images);
            $hotelResponse->setDescription($hotel['description'] ?? '');
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setDistance($hotel['distance'] ?? '');
            $hotelResponse->setLatitude($hotel['location']['coordinates']['latitude']);
            $hotelResponse->setLongitude($hotel['location']['coordinates']['longitude']);
            $hotelResponse->setRating($hotel['rating']);
            $hotelResponse->setAmenities($hotel['amenities'] ? array_map(function ($amenity) {
                return $amenity['name'];
            }, $amenities) : []);
            $hotelResponse->setGiataDestination($hotel['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = $hotelResponse->toArray();
        }

        return $contentSearchResponse;
    }

}
