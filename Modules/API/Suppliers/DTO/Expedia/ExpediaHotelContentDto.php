<?php

namespace Modules\API\Suppliers\DTO\Expedia;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
use Modules\API\Suppliers\DTO\SupplierContentDtoInterface;

class ExpediaHotelContentDto implements SupplierContentDtoInterface
{
    /**
     * @param array $supplierResponse
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $hotelResponse = new ContentSearchResponse();

            $images = [];
            $countImages = 0;

            if (is_array($hotel['images'])) {
                foreach ($hotel['images'] as $image) {
                    if ($countImages == 5) {
                        break;
                    }
                    if (is_array($image)) {
                        $images[] = $image['links']['350px']['href'];
                    } else {
                        $images[] = $image->links->{'350px'}->href;
                    }
                    $countImages++;
                }
            }

            $hotelResponse->setGiataHotelCode($hotel['giata_id'] ?? '');
            $hotelResponse->setImages($images);
            $hotelResponse->setDescription(isset($hotel['descriptions']) ? json_decode($hotel['descriptions'], true) : []);
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setDistance($hotel['distance'] ?? '');
            $hotelResponse->setLatitude($hotel['location']['coordinates']['latitude']);
            $hotelResponse->setLongitude($hotel['location']['coordinates']['longitude']);
            $hotelResponse->setRating($hotel['rating']);
            $amenities = $hotel['amenities'] ? json_decode(json_encode($hotel['amenities']), true) : [];
            if (!is_array($amenities)) {
                $amenities = [];
            }
            $hotelResponse->setAmenities(array_map(function ($amenity) {
                return $amenity['name'];
            }, $amenities));
            $hotelResponse->setGiataDestination($hotel['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = $hotelResponse->toArray();
        }

        return $contentSearchResponse;
    }
}
