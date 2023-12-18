<?php

declare(strict_types=1);

namespace Modules\API\Suppliers\DTO;

use Illuminate\Support\Facades\Log;

class IcePortalAssetDto
{
    /**
     * @param array $IceAssetResponse
     * @return array
     */
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
                    if (! str_contains($asset['links']['originalFileURL'], '.jpg')) {
                        $hotelImages[] = $asset['links']['originalFileURL'].'.jpg';
                    } else {
                        $hotelImages[] = $asset['links']['originalFileURL'];
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
        Log::debug('IcePortalAssetDto | IcePortalToAssets | IceAssetResponse ', [
            'IceAssetResponse' => $IceAssetResponse,
        ]);
        Log::debug('IcePortalAssetDto | IcePortalToAssets | asset ', [
            'hotelImages' => $hotelImages,
            'roomImages' => $roomImages,
            'roomAmenities' => $roomAmenities,
            'roomAmenitiesGeneral' => $roomAmenitiesGeneral,
            'hotelAmenities' => $hotelAmenities,
        ]);

        return compact('hotelImages', 'roomImages', 'roomAmenities', 'roomAmenitiesGeneral', 'hotelAmenities');
    }
}
