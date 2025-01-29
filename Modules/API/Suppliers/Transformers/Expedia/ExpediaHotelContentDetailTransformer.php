<?php

namespace Modules\API\Suppliers\Transformers\Expedia;

use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponseFactory;
use Modules\Enums\SupplierNameEnum;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ExpediaHotelContentDetailTransformer
{
    private const TA_CLIENT = 'https://developer.expediapartnersolutions.com/terms/en';

    private const TA_AGENT = 'https://developer.expediapartnersolutions.com/terms/agent/en/';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function ExpediaToContentDetailResponse(object $supplierResponse, int $giata_id): array
    {
        $contentResponse = [];

        $hotelImages = [];
        if (isset($supplierResponse->images) && is_iterable($supplierResponse->images)) {
            foreach ($supplierResponse->images as $image) {
                $hotelImages[] = $image->links->{'1000px'}->href;
            }
        } else {
            \Log::error('ExpediaHotelContentDetailTransformer | Probably an error with the expedia_content_slave table');
        }
        $viewAmenities = request()->get('category_amenities') === 'true';

        $address = $supplierResponse->address['line_1'].', '.
            $supplierResponse->address['city'];

        if ($postalCode = Arr::get($supplierResponse->address, 'postal_code')) {
            $address .= " - $postalCode";
        }

        $hotelResponse = ContentDetailResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages($hotelImages);
        $hotelResponse->setDescription($supplierResponse->description ?? '');
        $hotelResponse->setHotelName($supplierResponse->name);
        $hotelResponse->setDistance($supplierResponse->distance ?? '');
        $hotelResponse->setLatitude($supplierResponse->location['coordinates']['latitude']);
        $hotelResponse->setLongitude($supplierResponse->location['coordinates']['longitude']);
        $hotelResponse->setRating($supplierResponse->rating);
        $amenities = $supplierResponse->amenities ? json_decode(json_encode($supplierResponse->amenities), true) : [];
        if ($viewAmenities) {
            $hotelResponse->setAmenities($amenities);
        } else {
            $hotelResponse->setAmenities(array_map(function ($amenity) {
                return $amenity['name'];
            }, $amenities));
        }
        $hotelResponse->setGiataDestination($supplierResponse->city ?? '');
        $hotelResponse->setUserRating($supplierResponse->rating ?? '');
        $hotelResponse->setSpecialInstructions([
            'checkin' => $supplierResponse->checkin ?? null,
            'checkout' => $supplierResponse->checkout ?? null,
        ]);
        $hotelResponse->setCheckInTime($supplierResponse->checkin_time ?? '');
        $hotelResponse->setCheckOutTime($supplierResponse->checkout_time ?? '');

        $fees = $supplierResponse->fees ? json_decode(json_encode($supplierResponse->fees), true) : [];
        $policies = $supplierResponse->policies ? json_decode(json_encode($supplierResponse->policies), true) : [];

        // These validations are required because for some properties we are receiving [""] for fees/policies
        $fees = is_string($fees) ? [] : $fees;
        $policies = is_string($policies) ? [] : $policies;

        $hotelResponse->setHotelFees($fees);
        $hotelResponse->setPolicies($policies);
        $hotelResponse->setDescriptions($supplierResponse->descriptions ? json_decode(json_encode($supplierResponse->descriptions), true) : []);
        $hotelResponse->setAddress($supplierResponse->address ? $address : '');
        $hotelResponse->setSupplierInformation([
            'supplier_terms_and_conditions_client' => self::TA_CLIENT,
            'supplier_terms_and_conditions_agent' => self::TA_AGENT,
        ]);

        $rooms = [];
        if ($supplierResponse->rooms) {
            $_rooms = is_object($supplierResponse->rooms) ? $supplierResponse->rooms : json_decode($supplierResponse->rooms);

            // THIS IS A TEMP LOG TO TEST AN ISSUE
            if (! is_array($supplierResponse->rooms)) {
                \Log::info('ROOM DETAIL TEMP INFO', ['room' => $supplierResponse->rooms]);
            }

            foreach ($_rooms as $room) {
                $amenities = $room->amenities ? json_decode(json_encode($room->amenities), true) : [];
                $images = [];
                if (isset($room->images)) {
                    foreach ($room->images as $image) {
                        $images[] = $image->links->{'350px'}->href;
                    }
                }
                $roomResponse = ContentDetailRoomsResponseFactory::create();
                $roomResponse->setSupplierRoomId($room->id);
                $roomResponse->setSupplierRoomName($room->name);
                if ($viewAmenities) {
                    $roomResponse->setAmenities($amenities ?? []);
                } else {
                    $roomResponse->setAmenities(array_map(function ($amenity) {
                        return $amenity['name'];
                    }, $amenities));
                }
                $roomResponse->setImages($images);
                $roomResponse->setDescriptions($room->descriptions ? $room->descriptions->overview : '');
                $rooms[] = $roomResponse->toArray();
            }
        }
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }

    public function ExpediaArrayToContentDetailResponse(array $supplierResponse, int $giata_id): array
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
        $viewAmenities = request()->get('category_amenities') === 'true';

        $address = Arr::get($supplierResponse, 'address.line_1', '').', '.
            Arr::get($supplierResponse, 'address.city', '');

        if ($postalCode = Arr::get($supplierResponse, 'address.postal_code')) {
            $address .= " - $postalCode";
        }

        $hotelResponse = ContentDetailResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages($hotelImages);
        $hotelResponse->setHotelName(Arr::get($supplierResponse, 'name', ''));
        $hotelResponse->setDistance(Arr::get($supplierResponse, 'distance', ''));
        $hotelResponse->setLatitude(Arr::get($supplierResponse, 'location.coordinates.latitude', ''));
        $hotelResponse->setLongitude(Arr::get($supplierResponse, 'location.coordinates.longitude', ''));
        $hotelResponse->setRating(Arr::get($supplierResponse, 'rating', ''));
        $amenities = Arr::get($supplierResponse, 'amenities', []);
        if ($viewAmenities) {
            $hotelResponse->setAmenities($amenities);
        } else {
            $hotelResponse->setAmenities(array_map(function ($amenity) {
                return $amenity['name'];
            }, $amenities));
        }
        $hotelResponse->setGiataDestination(Arr::get($supplierResponse, 'city', ''));
        $hotelResponse->setUserRating(Arr::get($supplierResponse, 'rating', ''));
        $hotelResponse->setSpecialInstructions([
            'checkin' => Arr::get($supplierResponse, 'checkin', null),
            'checkout' => Arr::get($supplierResponse, 'checkout', null),
        ]);
        $hotelResponse->setCheckInTime(Arr::get($supplierResponse, 'checkin.begin_time', ''));
        $hotelResponse->setCheckOutTime(Arr::get($supplierResponse, 'checkout.time', ''));

        $fees = Arr::get($supplierResponse, 'fees', []);
        $policies = Arr::get($supplierResponse, 'policies', []);

        $fees = is_string($fees) ? [] : $fees;
        $policies = is_string($policies) ? [] : $policies;

        $hotelResponse->setHotelFees($fees);
        $hotelResponse->setPolicies($policies);
        $hotelResponse->setDescriptions(Arr::get($supplierResponse, 'descriptions', []));
        $hotelResponse->setAddress($address);
        $hotelResponse->setSupplierInformation([
            'supplier_terms_and_conditions_client' => self::TA_CLIENT,
            'supplier_terms_and_conditions_agent' => self::TA_AGENT,
        ]);

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
                $roomResponse->setSupplierRoomName(Arr::get($room, 'name', ''));
                if ($viewAmenities) {
                    $roomResponse->setAmenities($amenities);
                } else {
                    $roomResponse->setAmenities(array_map(function ($amenity) {
                        return $amenity['name'];
                    }, $amenities));
                }
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
