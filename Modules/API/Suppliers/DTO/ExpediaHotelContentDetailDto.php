<?php

namespace Modules\API\Suppliers\DTO;

use Modules\API\ContentAPI\ResponseModels\ContentDetailResponse;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ExpediaHotelContentDetailDto
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function ExpediaToContentDetailResponse(object $supplierResponse, int $giata_id): array
    {
        $contentResponse = [];

        $hotelImages = [];
        foreach ($supplierResponse->images as $image) {
            $hotelImages[] = $image->links->{'1000px'}->href;
        }
        $viewAmenities = request()->get('category_amenities') === 'true';

        $address = $supplierResponse->address['line_1'] . ', ' .
            $supplierResponse->address['city'] . ' - ' .
            $supplierResponse->address['postal_code'];

        $hotelResponse = new ContentDetailResponse();
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
        $hotelResponse->setSpecialInstructions($supplierResponse->room ?? []);
        $hotelResponse->setCheckInTime($supplierResponse->checkin_time ?? '');
        $hotelResponse->setCheckOutTime($supplierResponse->checkout_time ?? '');
        $hotelResponse->setHotelFees($supplierResponse->fees ? json_decode(json_encode($supplierResponse->fees), true) : []);
        $hotelResponse->setPolicies($supplierResponse->policies ? json_decode(json_encode($supplierResponse->policies), true) : []);
        $hotelResponse->setDescriptions($supplierResponse->descriptions ? json_decode(json_encode($supplierResponse->descriptions), true) : []);
        $hotelResponse->setAddress($supplierResponse->address ? $address : '');

        $rooms = [];
        foreach ($supplierResponse->rooms as $room) {
            $amenities = $room->amenities ? json_decode(json_encode($room->amenities), true) : [];
            $images = [];
            if (isset($room->images)) {
                foreach ($room->images as $image) {
                    $images[] = $image->links->{'350px'}->href;
                }
            }
            $roomResponse = new ContentDetailRoomsResponse();
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
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }
}
