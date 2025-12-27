<?php

declare(strict_types=1);

namespace Modules\API\Suppliers\IcePortal\Transformers;

use Illuminate\Support\Facades\Log;

class IcePortalAssetTransformer
{
    public function IcePortalToAssets(array $IceAssetResponse): array
    {
        $hotelImages = [];
        $roomImages = [];
        $roomAmenities = [];
        $roomAmenitiesGeneral = [];
        $hotelAmenities = [];
        foreach ($IceAssetResponse as $asset) {
            if (isset($asset['links']) && $asset['mediaType'] === 'PH') {
                if (! isset($asset['category'][0]['expediaCategory'])) {
                    continue;
                }
                // asset hotel
                if ($asset['category'][0]['expediaCategory']['name'] !== 'Room') {

                    // originalFileURL is not always set.
                    if (isset($asset['links']['cdnLinks'])) {
                        $hotelImages = array_merge($hotelImages, $asset['links']['cdnLinks']);
                    }

                    if (isset($asset['autoKeywords'])) {
                        $hotelAmenities = array_merge($hotelAmenities, $asset['autoKeywords']);
                    }
                } else { // asset room
                    if (isset($asset['rooms'])) {
                        foreach ($asset['rooms'] as $room) {
                            $roomImages[$room['roomID']][] = $asset['links']['mediaLinkURL'];
                        }
                    } else {
                        $roomAmenitiesGeneral[] = $asset['links']['mediaLinkURL'];
                    }
                    if (isset($asset['autoKeywords'])) {
                        $roomAmenities = array_merge($roomAmenities, $asset['autoKeywords']);
                    }
                }
            }
        }
        Log::debug('IcePortalAssetTransformer | IcePortalToAssets | IceAssetResponse ', [
            'IceAssetResponse' => $IceAssetResponse,
        ]);
        Log::debug('IcePortalAssetTransformer | IcePortalToAssets | asset ', [
            'hotelImages' => $hotelImages,
            'roomImages' => $roomImages,
            'roomAmenities' => $roomAmenities,
            'roomAmenitiesGeneral' => $roomAmenitiesGeneral,
            'hotelAmenities' => $hotelAmenities,
        ]);

        return compact('hotelImages', 'roomImages', 'roomAmenities', 'roomAmenitiesGeneral', 'hotelAmenities');
    }
}
