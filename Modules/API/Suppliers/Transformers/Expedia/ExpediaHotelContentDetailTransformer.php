<?php

namespace Modules\API\Suppliers\Transformers\Expedia;

use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponseFactory;
use Modules\Enums\SupplierNameEnum;

class ExpediaHotelContentDetailTransformer
{
    public function __construct(
        private readonly ExpediaTranformerService $expediaTranformerService
    ) {}

    public function ExpediaToContentDetailResponse(array $supplierResponse, int $giata_id): array
    {
        $contentResponse = [];

        $hotelImages = [];
        $images = Arr::get($supplierResponse, 'images', []);
        if (is_iterable($images)) {
            foreach ($images as $image) {
                $hotelImages[] = Arr::get($image, 'links.1000px.href', '');
            }
        } else {
            \Log::error('ExpediaHotelContentDetailTransformer | Probably an error with the expedia_content_slave table');
        }

        $address = Arr::get($supplierResponse, 'address.line_1', '').', '.
            Arr::get($supplierResponse, 'address.city', '');

        if ($postalCode = Arr::get($supplierResponse, 'address.postal_code')) {
            $address .= " - $postalCode";
        }

        $hotelResponse = ContentDetailResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages($hotelImages);
        $hotelResponse->setHotelName(Arr::get($supplierResponse, 'name', ''));
        $hotelResponse->setLatitude(Arr::get($supplierResponse, 'location.coordinates.latitude', ''));
        $hotelResponse->setLongitude(Arr::get($supplierResponse, 'location.coordinates.longitude', ''));
        $hotelResponse->setRating(Arr::get($supplierResponse, 'rating', ''));
        $amenities = Arr::get($supplierResponse, 'amenities', []);
        $hotelResponse->setAmenities(array_values(array_map(function ($amenity) {
            return [
                'name' => Arr::get($amenity, 'name'),
                'category' => Arr::get($amenity, 'categories.0', 'general'),
            ];
        }, $amenities)));
        $hotelResponse->setGiataDestination(Arr::get($supplierResponse, 'city', ''));
        $hotelResponse->setUserRating(Arr::get($supplierResponse, 'rating', ''));

        $attractionsData = Arr::get($supplierResponse, 'descriptions.attractions', []);
        $attractions = $this->expediaTranformerService->parseAttractions($attractionsData);
        $nearestAirports = array_filter($attractions, function ($attraction) {
            return str_contains($attraction['name'], 'Airport');
        });
        $hotelResponse->setNearestAirports(array_values($nearestAirports));

        $checkin = [];
        $checkinData = Arr::get($supplierResponse, 'checkin', []);
        foreach ($checkinData as $key => $value) {
            $checkin = array_merge($checkin, $this->expediaTranformerService->transformToNameValueArray([$key => $value], ['start_date', 'end_date'], 'checkin_'.$key));
        }
        $checkout = [];
        $checkoutData = Arr::get($supplierResponse, 'checkout', []);
        foreach ($checkoutData as $key => $value) {
            $checkout = array_merge($checkout, $this->expediaTranformerService->transformToNameValueArray([$key => $value], ['start_date', 'end_date'], 'checkout_'.$key));
        }

        $hotel_fees = $this->expediaTranformerService->transformToNameValueArray(Arr::get($supplierResponse, 'fees', []), ['start_date', 'end_date'], 'hotel_fees');
        $policies = $this->expediaTranformerService->transformToNameValueArray(Arr::get($supplierResponse, 'policies', []), ['start_date', 'end_date'], 'policies');
        $descriptions = $this->expediaTranformerService->transformToNameValueArray(Arr::get($supplierResponse, 'descriptions', []), ['start_date', 'end_date']);

        $hotelResponse->setDescriptions(array_merge($descriptions, $hotel_fees, $policies, $checkin, $checkout));

        $hotelResponse->setDrivers([['name' => 'Expedia', 'value' => true]]);

        $hotelResponse->setAddress($address);

        $rooms = [];
        $supplierRooms = Arr::get($supplierResponse, 'rooms', []);
        if ($supplierRooms) {
            $_rooms = is_array($supplierRooms) ? $supplierRooms : json_decode($supplierRooms, true);

            if (! is_array($supplierRooms)) {
                \Log::info('ROOM DETAIL TEMP INFO', ['room' => $supplierRooms]);
            }

            foreach ($_rooms as $room) {
                $amenities = Arr::get($room, 'amenities', []);
                $images = [];
                if (isset($room['images'])) {
                    foreach ($room['images'] as $image) {
                        $images[] = Arr::get($image, 'links.350px.href', '');
                    }
                }
                $roomResponse = ContentDetailRoomsResponseFactory::create();
                $roomResponse->setContentSupplier(SupplierNameEnum::EXPEDIA->value);
                $roomResponse->setSupplierRoomId(Arr::get($room, 'id', ''));
                $roomResponse->setUnifiedRoomCode(Arr::get($room, 'id', ''));
                $roomResponse->setSupplierRoomName(Arr::get($room, 'name', ''));
                $roomResponse->setAmenities(array_values(array_map(function ($amenity) {
                    return [
                        'name' => Arr::get($amenity, 'name'),
                        'category' => Arr::get($amenity, 'categories.0', 'general'),
                    ];
                }, $amenities)));
                $roomResponse->setImages($images);
                $roomResponse->setDescriptions(Arr::get($room, 'descriptions.overview', ''));
                $rooms[] = $roomResponse->toArray();
            }
        }
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }
}
