<?php

namespace Modules\API\Suppliers\Hilton\Transformers;

use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponseFactory;
use Modules\Enums\SupplierNameEnum;

class HiltonHotelContentDetailTransformer
{
    public function HiltonToContentDetailResponse(object $property, int $giata_id): array
    {
        /** @var HiltonTransformerService $service */
        $service = app(HiltonTransformerService::class);

        $address = trim("$property->address, $property->city, $property->country_code");
        $airport = $service->mapAirportData(Arr::get($property->props, 'locationDetails.airport', []));
        $descriptions = $service->mapDescriptions($property);
        $amenities = $service->mapAmenities(Arr::get($property->props, 'propDetail.propertyAttributes', []));

        $hotelResponse = ContentDetailResponseFactory::create();

        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages([]);
        $hotelResponse->setHotelName($property->name);
        $hotelResponse->setLongitude($property->longitude ?? '');
        $hotelResponse->setLatitude($property->latitude ?? '');
        $hotelResponse->setRating($property->star_rating ?? '');
        $hotelResponse->setNearestAirports($airport);
        $hotelResponse->setAmenities($amenities);
        $hotelResponse->setGiataDestination($property->city ?? '');
        $hotelResponse->setUserRating($property->props['userRating'] ?? '');
        $hotelResponse->setDescriptions($descriptions);
        $hotelResponse->setAddress($address);

        // rooms handle
        $rooms = $this->mapRooms(Arr::get($property->props, 'propDetail.guestRoomDescriptions', []));
        $hotelResponse->setRooms($rooms);

        $hotelResponse->setDrivers([['name' => 'Hilton', 'value' => true]]);

        return [$hotelResponse->toArray()];
    }

    private function mapRooms(array $guestRoomDescriptions): array
    {
        $rooms = [];

        foreach ($guestRoomDescriptions as $room) {
            $roomAmenities = array_map(function ($amenity) {
                return [
                    'name' => $amenity['roomAmenityDescription'],
                    'category' => 'general', // Assuming 'general' as the default category
                ];
            }, $room['roomAmenities'] ?? []);
            $roomResponse = ContentDetailRoomsResponseFactory::create();
            $roomResponse->setContentSupplier(SupplierNameEnum::HILTON->value);
            $roomResponse->setUnifiedRoomCode(Arr::get($room, 'roomTypeCode', ''));
            $roomResponse->setSupplierRoomName(Arr::get($room, 'bedClass', ''));
            $roomResponse->setSupplierRoomCode(Arr::get($room, 'roomTypeCode', ''));
            $roomResponse->setAmenities($roomAmenities);
            $roomResponse->setImages([]);
            $roomResponse->setDescriptions($room['enhancedDescription'] ?? '');

            $rooms[] = $roomResponse->toArray();
        }

        return $rooms;
    }
}
