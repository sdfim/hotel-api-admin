<?php

declare(strict_types=1);

namespace Modules\API\Suppliers\DTO;

use Modules\API\ContentAPI\ResponseModels\ContentDetailResponse;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponse;
use Modules\API\Suppliers\IceSuplier\IceHBSIClient;

class IcePortalHotelContentDetailDto
{
    /**
     * @var IceHBSIClient
     */
    private IceHBSIClient $client;

    /**
     *
     */
    public function __construct()
    {
        $this->client = new IceHBSIClient();
    }

    /**
     * @param object $supplierResponse
     * @param int $giata_id
     * @return array
     */
    public function HbsiToContentDetailResponse(object $supplierResponse, int $giata_id): array
    {
        $assets = $this->client->get('/v1/listings/' . $supplierResponse->listingID . '/assets', [
            'includeDisabledAssets' => 'true',
            'includeNotApprovedAssets' => 'true',
            'page' => '1',
            'pageSize' => '100',
        ]);
        if ($assets->failed()) {
            $assetsResponse = [];
        } else {
            $assetsResponse = $assets->json()['results'];
        }
        $rating = $assetsResponse[0]['rating'] ? (string)$assetsResponse[0]['rating'] : '';

        $contentResponse = [];

        $icePortalAssetDto = new IcePortalAssetDto();
        $result = $icePortalAssetDto->IcePortalToAssets($assetsResponse);

        $hotelImages = $result['hotelImages'];
        $roomImages = $result['roomImages'];
        $roomAmenities = $result['roomAmenities'];
        $roomAmenitiesGeneral = $result['roomAmenitiesGeneral'];
        $hotelAmenities = $result['hotelAmenities'];

        $address = $supplierResponse->address['addressLine1'] . ', ' .
            $supplierResponse->address['city'] . ' - ' .
            $supplierResponse->address['postalCode'];

        $hotelResponse = new ContentDetailResponse();
        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages($hotelImages);
        $hotelResponse->setDescription($supplierResponse->description ?? '');
        $hotelResponse->setHotelName($supplierResponse->name);
        $hotelResponse->setDistance($supplierResponse->distance ?? '');
        $hotelResponse->setLatitude($supplierResponse->address['latitude'] ?? '');
        $hotelResponse->setLongitude($supplierResponse->address['longitude'] ?? '');
        $hotelResponse->setRating($rating);
        $hotelResponse->setAmenities(array_unique($hotelAmenities));
        $hotelResponse->setGiataDestination($supplierResponse->address['city'] ?? '');
        $hotelResponse->setUserRating($rating);
        $hotelResponse->setSpecialInstructions($supplierResponse->room ?? []);
        $hotelResponse->setCheckInTime($supplierResponse->checkin_time ?? '');
        $hotelResponse->setCheckOutTime($supplierResponse->checkout_time ?? '');
        $hotelResponse->setHotelFees(isset($supplierResponse->fees) ?
            json_decode(json_encode($supplierResponse->fees), true) : []);
        $hotelResponse->setPolicies(isset($supplierResponse->policies) ?
            json_decode(json_encode($supplierResponse->policies), true) : []);
        $hotelResponse->setDescriptions(isset($supplierResponse->descriptions) ?
            json_decode(json_encode($supplierResponse->descriptions), true) : []);
        $hotelResponse->setAddress($supplierResponse->address ? $address : '');

        $rooms = [];
        foreach ($supplierResponse->roomTypes as $room) {
            $images = array_merge($roomImages[$room['roomID']] ?? [], $roomAmenitiesGeneral);
            $roomResponse = new ContentDetailRoomsResponse();
            $roomResponse->setSupplierRoomId($room['roomID']);
            $roomResponse->setSupplierRoomName($room['roomCode']);
            $roomResponse->setAmenities(array_unique($roomAmenities));
            $roomResponse->setImages($images);
            $roomResponse->setDescriptions($room['description'] ?? '');
            $rooms[] = $roomResponse->toArray();
        }
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }
}
