<?php

namespace Modules\API\Suppliers\HotelTrader\Transformers;

use App\Models\GiataGeography;
use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\Base\Transformers\SupplierContentTransformerInterface;

class HotelTraderContentTransformer implements SupplierContentTransformerInterface
{
    public function SupplierToContentSearchResponse(array $supplierResponse): array
    {
        /** @var HotelTraderTransformerService $service */
        $service = app(HotelTraderTransformerService::class);

        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $airport = $service->mapAirportData(Arr::get($hotel, 'props.locationDetails.airport', []));
            $descriptions = $service->mapDescriptions($hotel);
            $amenities = $service->mapAmenities(Arr::get($hotel, 'props.propDetail.propertyAttributes', []));

            $contentSearchResponseObj = ContentSearchResponseFactory::create();

            $contentSearchResponseObj->setGiataHotelCode(Arr::get($hotel, 'giata_id', 0));
            $contentSearchResponseObj->setHotelName(Arr::get($hotel, 'propertyName', ''));
            $contentSearchResponseObj->setLatitude(Arr::get($hotel, 'latitude', ''));
            $contentSearchResponseObj->setLongitude(Arr::get($hotel, 'longitude', ''));
            $contentSearchResponseObj->setRating((string) Arr::get($hotel, 'starRating', ''));
            $contentSearchResponseObj->setUserRating(Arr::get($hotel, 'guestRating', ''));
            $contentSearchResponseObj->setDescription($descriptions);
            $contentSearchResponseObj->setNumberRooms(is_array(Arr::get($hotel, 'rooms')) ? count($hotel['rooms']) : 0);
            $contentSearchResponseObj->setAmenities($amenities);
            $contentSearchResponseObj->setImages([]); // No images in input
            $contentSearchResponseObj->setNearestAirports($airport);
            $contentSearchResponseObj->setCurrency('');
            $contentSearchResponseObj->setGiataDestination(GiataGeography::where('city_name', Arr::get($hotel, 'city'))->value('city_id') ?? '');
            $contentSearchResponseObj->setWeight(0);
            $contentSearchResponseObj->setDepositInformation([]);
            $contentSearchResponseObj->setCancellationPolicies([]);
            $contentSearchResponseObj->setDrivers([]);

            $contentSearchResponse[] = $contentSearchResponseObj->toArray();
        }

        return $contentSearchResponse;
    }
}
