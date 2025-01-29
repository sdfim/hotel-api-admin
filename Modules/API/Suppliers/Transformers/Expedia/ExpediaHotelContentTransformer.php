<?php

namespace Modules\API\Suppliers\Transformers\Expedia;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;

class ExpediaHotelContentTransformer implements SupplierContentTransformerInterface
{
    private const TA_CLIENT = 'https://developer.expediapartnersolutions.com/terms/en';

    private const TA_AGENT = 'https://developer.expediapartnersolutions.com/terms/agent/en/';

    /**
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $hotelResponse = ContentSearchResponseFactory::create();

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
            if (! is_array($amenities)) {
                $amenities = [];
            }
            $hotelResponse->setAmenities(array_map(function ($amenity) {
                return $amenity['name'];
            }, $amenities));
            $hotelResponse->setGiataDestination($hotel['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');
            $hotelResponse->setImportantInformation([
                'checkin' => $hotel['checkin'] ? json_decode($hotel['checkin']) : '',
                'checkout' => $hotel['checkout'] ? json_decode($hotel['checkout']) : '',
                'fees' => $hotel['fees'] ? json_decode($hotel['fees']) : '',
                'policies' => $hotel['policies'] ? json_decode($hotel['policies']) : '',
            ]);
            $hotelResponse->setSupplierInformation([
                'supplier_terms_and_conditions_client' => self::TA_CLIENT,
                'supplier_terms_and_conditions_agent' => self::TA_AGENT,
            ]);

            $contentSearchResponse[] = $hotelResponse->toArray();
        }

        return $contentSearchResponse;
    }
}
