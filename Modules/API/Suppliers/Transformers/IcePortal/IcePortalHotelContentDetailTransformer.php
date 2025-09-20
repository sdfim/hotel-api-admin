<?php

declare(strict_types=1);

namespace Modules\API\Suppliers\Transformers\IcePortal;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\API\ContentAPI\ResponseModels\ContentDetailResponseFactory;
use Modules\API\ContentAPI\ResponseModels\ContentDetailRoomsResponseFactory;
use Modules\API\Suppliers\IceSupplier\IceHBSIClient;
use Modules\Enums\SupplierNameEnum;

class IcePortalHotelContentDetailTransformer
{
    public function __construct(
        private readonly IceHBSIClient $client,
        private readonly IcePortalAssetTransformer $icePortalAssetTransformer,
    ) {}

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

        $result = $this->icePortalAssetTransformer->IcePortalToAssets($assetsResponse, $roomTypeCodes);

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
        $hotelResponse->setLatitude($supplierResponse->address['latitude'] ?? '');
        $hotelResponse->setLongitude($supplierResponse->address['longitude'] ?? '');
        $hotelResponse->setRating($rating);
        $hotelResponse->setAmenities(array_unique($hotelAmenities));
        $hotelResponse->setGiataDestination($supplierResponse->address['city'] ?? '');
        $hotelResponse->setUserRating($rating);

        $fees = isset($supplierResponse->fees) ? json_decode(json_encode($supplierResponse->fees), true) : [];
        $policies = isset($supplierResponse->policies) ? json_decode(json_encode($supplierResponse->policies), true) : [];
        $descriptions = isset($supplierResponse->descriptions) ? json_decode(json_encode($supplierResponse->descriptions), true) : [];
        $descriptions = array_merge($fees, $policies, $descriptions);
        $hotelResponse->setDescriptions($descriptions);
        $hotelResponse->setAddress($supplierResponse->address ? $address : '');

        $rooms = [];
        foreach ($supplierResponse->roomTypes as $room) {

            if (! empty($roomTypeCodes) && ! in_array($room['roomCode'], $roomTypeCodes)) {
                continue;
            }

            $images = array_merge($roomImages[$room['roomID']] ?? [], $roomAmenitiesGeneral);
            $roomResponse = ContentDetailRoomsResponseFactory::create();
            $roomResponse->setSupplierRoomId($room['roomID']);
            $roomResponse->setSupplierRoomCode($room['roomCode']);
            $roomResponse->setUnifiedRoomCode($room['roomCode']);
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

        $result = $this->icePortalAssetTransformer->IcePortalToAssets($assetsResponse, $roomTypeCodes);

        $hotelImages = $result['hotelImages'];
        $roomImages = $result['roomImages'];
        $roomAmenities = $result['roomAmenities'];
        $roomAmenitiesGeneral = $result['roomAmenitiesGeneral'];

        $hotelAmenities = array_map(function ($amenity) {
            $cacheKey = 'config_attribute_'.$amenity;
            $configAttribute = Cache::get($cacheKey);
            if (! $configAttribute) {
                $configAttribute = ConfigAttribute::where('name', $amenity)->with('categories')->first();
                Cache::put($cacheKey, $configAttribute, now()->addHours(12));
            }

            $category = ($configAttribute && $configAttribute->categories && $configAttribute->categories->count())
                ? $configAttribute->categories->first()->name
                : 'general';

            return [
                'name' => $amenity,
                'category' => $category,
            ];
        }, array_unique($result['hotelAmenities']));

        $address = Arr::get($supplierResponse, 'addressLine1', '').', '.
            Arr::get($supplierResponse, 'city', '').' - '.
            Arr::get($supplierResponse, 'postalCode', '');

        $hotelResponse = ContentDetailResponseFactory::create();
        $hotelResponse->setGiataHotelCode($giata_id);
        $hotelResponse->setImages($hotelImages);
        $hotelResponse->setDescription(Arr::get($supplierResponse, 'description', ''));
        $hotelResponse->setHotelName(Arr::get($supplierResponse, 'name', ''));
        $hotelResponse->setLatitude(Arr::get($supplierResponse, 'latitude', ''));
        $hotelResponse->setLongitude(Arr::get($supplierResponse, 'longitude', ''));
        $hotelResponse->setRating($rating);
        $hotelResponse->setAmenities($hotelAmenities);
        $hotelResponse->setGiataDestination(Arr::get($supplierResponse, 'city', ''));
        $hotelResponse->setUserRating($rating);
        $hotelResponse->setSpecialInstructions(Arr::get($supplierResponse, 'room', []));

        $fees = Arr::get($supplierResponse, 'fees', []);
        $policies = Arr::get($supplierResponse, 'policies', []);
        $descriptions = Arr::get($supplierResponse, 'descriptions', []);
        $descriptions = array_merge($fees, $policies, $descriptions);

        $hotelResponse->setDescriptions($descriptions);
        $hotelResponse->setAddress($address);

        $rooms = [];
        foreach (Arr::get($supplierResponse, 'roomTypes', []) as $room) {
            if (! empty($roomTypeCodes) && ! in_array(Arr::get($room, 'roomCode'), $roomTypeCodes)) {
                continue;
            }

            $inpetDescriptions = Arr::get($room, 'description', '');
            $inpetDescriptionsArray = explode(',', $inpetDescriptions);
            $roomName = Arr::get($inpetDescriptionsArray, '0', '');
            $roomImages = array_column(Arr::get($room, 'roomTypeAssets', []), 'links');
            $mediaLinks = array_column($roomImages, 'mediaLinkURL');

            $roomResponse = ContentDetailRoomsResponseFactory::create();
            $roomResponse->setContentSupplier(SupplierNameEnum::ICE_PORTAL->value);
            $roomResponse->setSupplierRoomId(Arr::get($room, 'roomID'));
            $roomResponse->setSupplierRoomCode(Arr::get($room, 'roomCode'));
            $roomResponse->setUnifiedRoomCode(Arr::get($room, 'roomCode'));
            $roomResponse->setAmenities(array_values(array_map(function ($amenity) {
                return [
                    'name' => $amenity,
                    'category' => 'general',
                ];
            }, $roomAmenities)));
            $roomResponse->setImages($mediaLinks);
            $roomResponse->setDescriptions($inpetDescriptions);
            $roomResponse->setSupplierRoomName($roomName);
            $rooms[] = $roomResponse->toArray();
        }
        $hotelResponse->setRooms($rooms);

        $contentResponse[] = $hotelResponse->toArray();

        return $contentResponse;
    }
}
