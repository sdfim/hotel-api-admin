<?php

namespace Modules\API\Suppliers\DTO;

use Modules\API\ContentAPI\ResponseModels\ContentDetailResponse;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ExpediaContentDetailDto
{

    /**
     * @param object $supplierResponse
     * @param int $giata_id
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function ExpediaToContentDetailResponse(object $supplierResponse, int $giata_id): array
    {
        $contentResponse = [];

        $hotelImages = [];
        foreach ($supplierResponse->images as $image) {
            $hotelImages[] = $image['links']['350px']['href'];
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
        if ($viewAmenities) {
            $hotelResponse->setAmenities($supplierResponse->amenities ?? []);
        } else {
            $hotelResponse->setAmenities($supplierResponse->amenities ? array_map(function ($amenity) {
                return $amenity['name'];
            }, $supplierResponse->amenities) : []);
        }
        $hotelResponse->setGiataDestination($supplierResponse->city ?? '');
        $hotelResponse->setUserRating($supplierResponse->rating ?? '');
        $hotelResponse->setSpecialInstructions($supplierResponse->room ?? []);
        $hotelResponse->setCheckInTime($supplierResponse->checkin_time ?? '');
        $hotelResponse->setCheckOutTime($supplierResponse->checkout_time ?? '');
        $hotelResponse->setHotelFees($supplierResponse->fees ?? []);
        $hotelResponse->setPolicies($supplierResponse->policies ?? []);
        $hotelResponse->setDescriptions($supplierResponse->descriptions ?? []);
        $hotelResponse->setAddress($supplierResponse->address ? $address : '');

        $rooms = [];
        foreach ($supplierResponse->rooms as $room) {
            $amenities = $room['amenities'] ?? [];
            $images = [];
            foreach ($room['images'] as $image) {
                $images[] = $image['links']['350px']['href'];
            }
            $roomResponse = new ContentDetailRoomsResponse();
            $roomResponse->setSupplierRoomId($room['id']);
            $roomResponse->setSupplierRoomName($room['name']);
            if ($viewAmenities) {
                $roomResponse->setAmenities($room['amenities'] ?? []);
            } else {
                $roomResponse->setAmenities($room['amenities'] ? array_map(function ($amenity) {
                    return $amenity['name'];
                }, $amenities) : []);
            }
            $roomResponse->setImages($images);
            $roomResponse->setDescriptions($room['descriptions'] ? $room['descriptions']['overview'] : '');
            $rooms[] = $roomResponse->toArray();
        }
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }
}
