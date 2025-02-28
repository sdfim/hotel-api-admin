<?php

namespace Modules\API\Suppliers\Transformers\Expedia;

use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;

class ExpediaHotelContentTransformer implements SupplierContentTransformerInterface
{
    public function __construct(
        private readonly ExpediaTranformerService $expediaTranformerService
    ) {}

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

            $checkin = [];
            $checkinData = json_decode(Arr::get($hotel, 'checkin', []), true);
            foreach ($checkinData ?? [] as $key => $value) {
                $checkin = array_merge($checkin, $this->expediaTranformerService->transformToNameValueArray([$key => $value], ['start_date', 'end_date'], 'checkin_'.$key));
            }
            $checkout = [];
            $checkoutData = json_decode(Arr::get($hotel, 'checkout', []), true);
            foreach ($checkoutData ?? [] as $key => $value) {
                $checkout = array_merge($checkout, $this->expediaTranformerService->transformToNameValueArray([$key => $value], ['start_date', 'end_date'], 'checkout_'.$key));
            }

            $descriptionsData = json_decode(Arr::get($hotel, 'descriptions', []), true);

            $hotel_fees = $this->expediaTranformerService->transformToNameValueArray(json_decode(Arr::get($hotel, 'fees', []), true), ['start_date', 'end_date'], 'hotel_fees');
            $policies = $this->expediaTranformerService->transformToNameValueArray(json_decode(Arr::get($hotel, 'policies', []), true), ['start_date', 'end_date'], 'policies');
            $descriptions = $this->expediaTranformerService->transformToNameValueArray($descriptionsData, ['start_date', 'end_date']);

            $attractionsData = Arr::get($descriptionsData, 'attractions', '');
            $attractions = $this->expediaTranformerService->parseAttractions($attractionsData);
            $nearestAirports = array_filter($attractions, function ($attraction) {
                return strpos($attraction['name'], 'Airport') !== false;
            });
            $hotelResponse->setNearestAirports(array_values($nearestAirports));

            $descriptions = array_merge($descriptions, $hotel_fees, $policies, $checkin, $checkout);
            $descriptions = array_values(array_filter($descriptions, fn ($description) => $description !== null));

            $hotelResponse->setGiataHotelCode($hotel['giata_id'] ?? '');
            $hotelResponse->setImages($images);
            $hotelResponse->setDescription($descriptions);
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setLatitude($hotel['location']['coordinates']['latitude']);
            $hotelResponse->setLongitude($hotel['location']['coordinates']['longitude']);
            $hotelResponse->setRating($hotel['rating']);
            $amenities = $hotel['amenities'] ? json_decode(json_encode($hotel['amenities']), true) : [];
            if (! is_array($amenities)) {
                $amenities = [];
            }
            $hotelResponse->setAmenities(array_values(array_map(function ($amenity) {
                return $amenity['name'];
            }, $amenities)));
            $hotelResponse->setGiataDestination($hotel['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = $hotelResponse->toArray();
        }

        return $contentSearchResponse;
    }
}
