<?php

namespace Modules\API\Suppliers\IcePortal\Adapters;

use App\Models\IcePortalPropertyAsset;
use App\Models\Mapping;
use Illuminate\Support\Arr;
use Modules\API\Suppliers\Contracts\Hotel\ContentV1\HotelContentV1SupplierInterface;
use Modules\API\Suppliers\IcePortal\Transformers\IcePortalHotelContentDetailTransformer;
use Modules\Enums\SupplierNameEnum;

class IcePortalHotelContentV1Adapter implements HotelContentV1SupplierInterface
{
    public function __construct(
        protected readonly IcePortalHotelAdapter $icePortal,
        protected readonly IcePortalHotelContentDetailTransformer $icePortalHotelContentDetailTransformer,
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::ICE_PORTAL;
    }

    public function getResults(array $giataCodes): array
    {
        $resultsIcePortal = $this->icePortal->details($giataCodes);

        $results = [];
        foreach ($resultsIcePortal as $giataId => $item) {
            $contentDetailResponse = $this->icePortalHotelContentDetailTransformer
                ->HbsiToContentDetailResponseWithAssets($item, $giataId, $item['assets']);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];
        $icePortalCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::ICE_PORTAL->value)
            ->first()?->supplier_id;

        $icePortalData = IcePortalPropertyAsset::where('listingID', $icePortalCode)->first();
        $icePortalData = $icePortalData ? $icePortalData->toArray() : [];

        $rooms = $icePortalData['roomTypes'] ?? [];

        foreach ($rooms as $room) {
            $roomId = $room['roomCode'] ?? null;

            // Take description string
            $description = Arr::get($room, 'description', '');
            $parts = array_map('trim', explode(',', $description));

            // The last element is considered as a "view", all others make up the name
            $lastPart = array_pop($parts);
            $views = $lastPart ?? '';

            $roomsData[] = [
                'id' => $roomId,
                'name' => $description, // room name without the last part
                'descriptions' => $description, // full original string
                'area' => $room['area'] ?? null, // area is not provided
                'views' => $views, // last part (if exists) is considered a view
                'bed_groups' => $room['bed_groups'] ?? [],
                'amenities' => $room['amenities'] ?? [],
                'supplier' => SupplierNameEnum::ICE_PORTAL->value,
            ];
        }

        return $roomsData;
    }
}
