<?php

namespace Modules\API\Suppliers\Transformers\HotelTrader;

use App\Models\GiataGeography;
use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\Transformers\Hilton\HiltonTransformerService;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;

class HotelTraderContentTransformer implements SupplierContentTransformerInterface
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

            $images = Arr::get($hotel, 'images');

            $contentSearchResponseObj = ContentSearchResponseFactory::create();

            $contentSearchResponseObj->setGiataHotelCode(Arr::get($hotel, 'giata_id', 0));
            $contentSearchResponseObj->setImages(is_array($images) ? $images : []);
            $contentSearchResponseObj->setDescription($descriptions ?? []);
            $contentSearchResponseObj->setNearestAirports($airport ?? []);
            $contentSearchResponseObj->setHotelName(Arr::get($hotel, 'name', ''));
            $contentSearchResponseObj->setLatitude(Arr::get($hotel, 'latitude', ''));
            $contentSearchResponseObj->setLongitude(Arr::get($hotel, 'longitude', ''));
            $contentSearchResponseObj->setRating((string) Arr::get($hotel, 'star_rating', ''));
            $contentSearchResponseObj->setCurrency(Arr::get($hotel, 'default_currency_code', ''));
            $contentSearchResponseObj->setNumberRooms(Arr::get($hotel, 'number_of_rooms', 0));
            $contentSearchResponseObj->setAmenities(Arr::get($hotel, 'amenities', $amenities ?? []));
            $contentSearchResponseObj->setGiataDestination(GiataGeography::where('city_name', Arr::get($hotel, 'city'))->value('city_id') ?? '');
            $contentSearchResponseObj->setUserRating(''); // No mapping in provided data
            $contentSearchResponseObj->setWeight(0); // No mapping in provided data
            $contentSearchResponseObj->setDepositInformation([]); // No mapping in provided data
            $contentSearchResponseObj->setCancellationPolicies([]); // No mapping in provided data
            $contentSearchResponseObj->setDrivers([]); // No mapping in provided data

            $contentSearchResponse[] = $contentSearchResponseObj->toArray();
        }

        return $contentSearchResponse;
    }
}
