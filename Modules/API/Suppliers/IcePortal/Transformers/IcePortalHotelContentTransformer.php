<?php

namespace Modules\API\Suppliers\IcePortal\Transformers;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
use Modules\API\ContentAPI\ResponseModels\ContentSearchResponseFactory;
use Modules\API\Suppliers\Base\Transformers\SupplierContentTransformerInterface;
use Modules\Enums\SupplierNameEnum;

class IcePortalHotelContentTransformer implements SupplierContentTransformerInterface
{
    private IcePortalAssetTransformer $icePortalAssetTransformer;

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::ICE_PORTAL;
    }

    public function __construct(IcePortalAssetTransformer $icePortalAssetTransformer)
    {
        $this->icePortalAssetTransformer = $icePortalAssetTransformer;
    }

    /**
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $hotelResponse = ContentSearchResponseFactory::create();

            $assetsResponse = isset($hotel['assets']['results']) ? $hotel['assets']['results'] : [];
            $roomTypeCodes = isset($hotel['roomTypeCodes']) ? $hotel['roomTypeCodes'] : [];
            $result = $this->icePortalAssetTransformer->IcePortalToAssets($assetsResponse, $roomTypeCodes);

            $images = $result['hotelImages'] ?? [];
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
            }, array_unique($result['hotelAmenities'] ?? []));

            $fees = Arr::get($hotel, 'fees', []);
            $policies = Arr::get($hotel, 'policies', []);
            $descriptions = Arr::get($hotel, 'descriptions', []);
            $descriptions = array_merge($fees, $policies, $descriptions);

            $address = [
                'addressLine1' => $hotel['addressLine1'] ?? '',
                'city' => $hotel['city'] ?? '',
                'country' => $hotel['country'] ?? '',
                'postalCode' => $hotel['postalCode'] ?? '',
                'latitude' => $hotel['latitude'] ?? '',
                'longitude' => $hotel['longitude'] ?? '',
            ];

            $hotelResponse->setGiataHotelCode(isset($hotel['giata_id']) ? intval($hotel['giata_id']) : 0);
            $hotelResponse->setImages($images);
            $hotelResponse->setDescription($descriptions);
            $hotelResponse->setHotelName($hotel['name'] ?? '');
            $hotelResponse->setLatitude($address['latitude']);
            $hotelResponse->setLongitude($address['longitude']);
            $hotelResponse->setRating($hotel['rating'] ?? '');
            $hotelResponse->setAmenities($hotelAmenities);
            $hotelResponse->setGiataDestination($address['city']);
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = array_merge($hotelResponse->toArray(), [
                'perc' => $hotel['perc'] ?? 0,
                'address' => $address,
            ]);
        }

        return $contentSearchResponse;
    }

    public function OldSupplierToContentSearchResponse(array $supplierResponse): array
    {
        $contentSearchResponse = [];

        foreach ($supplierResponse as $hotel) {
            $hotelResponse = ContentSearchResponseFactory::create();

            $images = is_array($hotel['images']) ? $hotel['images'] : json_decode($hotel['images'], true);
            $amenities = is_array($hotel['amenities']) ? $hotel['amenities'] : json_decode($hotel['amenities'], true);

            $fees = Arr::get($hotel, 'fees', []);
            $policies = Arr::get($hotel, 'policies', []);
            $descriptions = Arr::get($hotel, 'descriptions', []);
            $descriptions = array_merge($fees, $policies, $descriptions);

            $hotelResponse->setGiataHotelCode(isset($hotel['giata_id']) ? intval($hotel['giata_id']) : 0);
            $hotelResponse->setImages($images ?? []);
            $hotelResponse->setDescription($descriptions);
            $hotelResponse->setHotelName($hotel['name']);
            $hotelResponse->setLatitude($hotel['address']['latitude'] ?? $hotel['latitude'] ?? '');
            $hotelResponse->setLongitude($hotel['address']['longitude'] ?? $hotel['latitude'] ?? '');
            $hotelResponse->setRating($hotel['rating'] ?? '');
            $hotelResponse->setAmenities($amenities ?? []);
            $hotelResponse->setGiataDestination($hotel['address']['city'] ?? $hotel['city'] ?? '');
            $hotelResponse->setUserRating($hotel['rating'] ?? '');

            $contentSearchResponse[] = array_merge($hotelResponse->toArray(), ['perc' => $hotel['perc'] ?? 0]);
        }

        return $contentSearchResponse;
    }
}
