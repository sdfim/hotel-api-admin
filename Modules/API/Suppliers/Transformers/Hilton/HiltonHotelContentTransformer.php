<?php

namespace Modules\API\Suppliers\Transformers\Hilton;

use App\Models\GiataGeography;
use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;

class HiltonHotelContentTransformer implements SupplierContentTransformerInterface
{
    public function SupplierToContentSearchResponse(array $supplierResponse): array
    {
        /** @var HiltonTransformerService $service */
        $service = app(HiltonTransformerService::class);

        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $airport = $service->mapAirportData(Arr::get($hotel, 'props.locationDetails.airport', []));
            $descriptions = $service->mapDescriptions($hotel);
            $amenities = $service->mapAmenities(Arr::get($hotel, 'props.propDetail.propertyAttributes', []));

            $hotelResponse = ContentSearchResponseFactory::create();

            $hotelResponse->setGiataHotelCode($hotel['giata_id']);
            $hotelResponse->setImages([]);
            $hotelResponse->setDescription($descriptions);
            $hotelResponse->setNearestAirports($airport);
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setLatitude(Arr::get($hotel, 'latitude', Arr::get($hotel, 'props.locationDetails.onlineLatitude', '')));
            $hotelResponse->setLongitude(Arr::get($hotel, 'longitude', Arr::get($hotel, 'props.locationDetails.onlineLongitude', '')));
            $hotelResponse->setRating(Arr::get($hotel, 'star_rating', Arr::get($hotel, 'props.propDetail.ratings.starRating', '')));
            $hotelResponse->setAmenities($amenities);
            $hotelResponse->setGiataDestination(GiataGeography::where('city_name', $hotel['city'])->value('city_id') ?? '');
            $hotelResponse->setUserRating('');

            $contentSearchResponse[] = $hotelResponse->toArray();
        }

        return $contentSearchResponse;
    }
}
