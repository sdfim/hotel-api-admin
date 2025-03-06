<?php

namespace Modules\API\Suppliers\Transformers\IcePortal;

use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;

class IcePortalHotelContentTransformer implements SupplierContentTransformerInterface
{
    /**
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $hotelResponse = ContentSearchResponseFactory::create();

            $images = is_array($hotel['images']) ? $hotel['images'] : json_decode($hotel['images'], true);
            $amenities = is_array($hotel['amenities']) ? $hotel['amenities'] : json_decode($hotel['amenities'], true);

            $fees = Arr::get($hotel, 'fees', []);
            $policies = Arr::get($hotel, 'policies', []);
            $descriptions = Arr::get($hotel, 'descriptions', []);
            $descriptions = array_merge($fees, $policies, $descriptions);

            $hotelResponse->setGiataHotelCode(isset($hotel['giata_id']) ? intval($hotel['giata_id']) : 0);
            $hotelResponse->setImages($images ?? []);
            $hotelResponse->setDescription($descriptions);
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setLatitude($hotel['address']['latitude'] ?? $hotel['latitude'] ?? '');
            $hotelResponse->setLongitude($hotel['address']['longitude'] ?? $hotel['latitude'] ?? '');
            $hotelResponse->setRating($hotel['rating'] ?? '');
            $hotelResponse->setAmenities($amenities ?? []);
            $hotelResponse->setGiataDestination($hotel['address']['city'] ?? $hotel['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = array_merge($hotelResponse->toArray(), ['perc' => $hotel['perc'] ?? 0]);
        }

        return $contentSearchResponse;
    }
}
