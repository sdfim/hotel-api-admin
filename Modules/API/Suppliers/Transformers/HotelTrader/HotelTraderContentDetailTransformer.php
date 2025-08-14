<?php

namespace Modules\API\Suppliers\Transformers\HotelTrader;

use App\Models\HotelTraderProperty;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponseFactory;

class HotelTraderContentDetailTransformer
{
    public function HotelTraderToContentDetailResponse(HotelTraderProperty $property, int $giata_id): array
    {
        /** @var HotelTraderTransformerService $service */
        $service = app(HotelTraderTransformerService::class);

        $descriptions = $service->mapDescriptions($property);

        $address = trim(
            ($property->address1 ?? '').', '.
            ($property->address2 ?? '').', '.
            ($property->city ?? '').', '.
            ($property->state ?? '').', '.
            ($property->countryCode ?? '').', '.
            ($property->zipCode ?? '')
        );

        $hotelResponse = ContentDetailResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages([]); // Add images if available
        $hotelResponse->setHotelName($property->propertyName ?? '');
        $hotelResponse->setLongitude($property->longitude ?? '');
        $hotelResponse->setLatitude($property->latitude ?? '');
        $hotelResponse->setRating($property->starRating ?? '');
        $hotelResponse->setNearestAirports([]); // No airport info in supplier data
        $hotelResponse->setAmenities([]); // Add amenities if available
        $hotelResponse->setGiataDestination($property->city ?? '');
        $hotelResponse->setUserRating($property->guestRating ?? '');
        $hotelResponse->setDescriptions($descriptions);
        $hotelResponse->setAddress($address);

        // rooms handle
        $rooms = $this->mapRooms($property->rooms ?? []);
        $hotelResponse->setRooms($rooms);

        $hotelResponse->setDrivers([['name' => 'HotelTrader', 'value' => true]]);

        return [$hotelResponse->toArray()];
    }

    private function mapRooms($roomsData): array
    {
        $rooms = [];
        foreach ($roomsData as $room) {
            $roomResponse = ContentDetailRoomsResponseFactory::create();
            $roomResponse->setContentSupplier('HotelTrader');
            $roomResponse->setUnifiedRoomCode($room['roomCode'] ?? $room->roomCode ?? '');
            $roomResponse->setSupplierRoomName($room['displayName'] ?? $room->displayName ?? '');
            $roomResponse->setSupplierRoomCode($room['roomCode'] ?? $room->roomCode ?? '');
            $roomResponse->setAmenities([]); // Add amenities if available
            $roomResponse->setImages([]); // Add images if available
            $roomResponse->setDescriptions($room['shortDesc'] ?? $room->shortDesc ?? '');
            $rooms[] = $roomResponse->toArray();
        }

        return $rooms;
    }
}
