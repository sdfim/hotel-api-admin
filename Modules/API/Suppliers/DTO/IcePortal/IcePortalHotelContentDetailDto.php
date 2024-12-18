<?php

declare(strict_types=1);

namespace Modules\API\Suppliers\DTO\IcePortal;

use Illuminate\Support\Arr;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponseFactory;
use Modules\API\Suppliers\IceSuplier\IceHBSIClient;
use Modules\Enums\SupplierNameEnum;

class IcePortalHotelContentDetailDto
{
    private IceHBSIClient $client;

    public function __construct()
    {
        $this->client = new IceHBSIClient();
    }

    public function HbsiToContentDetailResponse(object $supplierResponse, int $giata_id, array $roomTypeCodes = []): array
    {
        $assets = $this->client->get('/v1/listings/'.$supplierResponse->listingID.'/assets', [
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
        $rating = $assetsResponse[0]['rating'] ? (string) $assetsResponse[0]['rating'] : '';

        $contentResponse = [];

        $icePortalAssetDto = new IcePortalAssetDto();
        $result = $icePortalAssetDto->IcePortalToAssets($assetsResponse, $roomTypeCodes);

        $hotelImages = $result['hotelImages'];
        $roomImages = $result['roomImages'];
        $roomAmenities = $result['roomAmenities'];
        $roomAmenitiesGeneral = $result['roomAmenitiesGeneral'];
        $hotelAmenities = $result['hotelAmenities'];

        $address = $supplierResponse->address['addressLine1'].', '.
            $supplierResponse->address['city'].' - '.
            $supplierResponse->address['postalCode'];

        $hotelResponse = ContentDetailResponseFactory::create();
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

            if (!empty($roomTypeCodes) && !in_array($room['roomCode'], $roomTypeCodes))
            {
                continue;
            }

            $images = array_merge($roomImages[$room['roomID']] ?? [], $roomAmenitiesGeneral);
            $roomResponse = ContentDetailRoomsResponseFactory::create();
            $roomResponse->setSupplierRoomId($room['roomID']);
            $roomResponse->setSupplierRoomCode($room['roomCode']);
            $roomResponse->setAmenities(array_unique($roomAmenities));
            $roomResponse->setImages($images);
            $roomResponse->setDescriptions($room['description'] ?? '');
            $rooms[] = $roomResponse->toArray();
        }
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }

    public function HbsiToContentDetailResponseWithAssets(array $supplierResponse, int $giata_id, array $assets, array $roomTypeCodes = []): array
    {
        $assetsResponse = empty($assets) ? [] : $assets['results'];
        $rating = (string) Arr::get($assetsResponse, '0.rating', '');

        $contentResponse = [];

        $icePortalAssetDto = new IcePortalAssetDto();
        $result = $icePortalAssetDto->IcePortalToAssets($assetsResponse, $roomTypeCodes);

        $hotelImages = $result['hotelImages'];
        $roomImages = $result['roomImages'];
        $roomAmenities = $result['roomAmenities'];
        $roomAmenitiesGeneral = $result['roomAmenitiesGeneral'];
        $hotelAmenities = $result['hotelAmenities'];

        $address = Arr::get($supplierResponse, 'address.addressLine1', '') . ', ' .
            Arr::get($supplierResponse, 'address.city', '') . ' - ' .
            Arr::get($supplierResponse, 'address.postalCode', '');

        $hotelResponse = ContentDetailResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages($hotelImages);
        $hotelResponse->setDescription(Arr::get($supplierResponse, 'description', ''));
        $hotelResponse->setHotelName(Arr::get($supplierResponse, 'name', ''));
        $hotelResponse->setDistance(Arr::get($supplierResponse, 'distance', ''));
        $hotelResponse->setLatitude(Arr::get($supplierResponse, 'address.latitude', ''));
        $hotelResponse->setLongitude(Arr::get($supplierResponse, 'address.longitude', ''));
        $hotelResponse->setRating($rating);
        $hotelResponse->setAmenities(array_unique($hotelAmenities));
        $hotelResponse->setGiataDestination(Arr::get($supplierResponse, 'address.city', ''));
        $hotelResponse->setUserRating($rating);
        $hotelResponse->setSpecialInstructions(Arr::get($supplierResponse, 'room', []));
        $hotelResponse->setCheckInTime(Arr::get($supplierResponse, 'checkin_time', ''));
        $hotelResponse->setCheckOutTime(Arr::get($supplierResponse, 'checkout_time', ''));
        $hotelResponse->setHotelFees(Arr::get($supplierResponse, 'fees', []));
        $hotelResponse->setPolicies(Arr::get($supplierResponse, 'policies', []));
        $hotelResponse->setDescriptions(Arr::get($supplierResponse, 'descriptions', []));
        $hotelResponse->setAddress($address);

        $rooms = [];
        foreach (Arr::get($supplierResponse, 'roomTypes', []) as $room) {
            if (!empty($roomTypeCodes) && !in_array(Arr::get($room, 'roomCode'), $roomTypeCodes)) {
                continue;
            }

            $images = array_merge(Arr::get($roomImages, Arr::get($room, 'roomID'), []), $roomAmenitiesGeneral);
            $roomResponse = ContentDetailRoomsResponseFactory::create();
            $roomResponse->setContentSupplier(SupplierNameEnum::ICE_PORTAL->value);
            $roomResponse->setSupplierRoomId(Arr::get($room, 'roomID'));
            $roomResponse->setSupplierRoomCode(Arr::get($room, 'roomCode'));
            $roomResponse->setAmenities(array_unique($roomAmenities));
            $roomResponse->setImages($images);
            $roomResponse->setDescriptions(Arr::get($room, 'description', ''));
            $rooms[] = $roomResponse->toArray();
        }
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }}
