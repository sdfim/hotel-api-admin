<?php

namespace Modules\HotelContentRepository\Services\Suppliers;

use App\Models\HiltonProperty;
use App\Models\Mapping;
use Modules\API\Suppliers\Transformers\Hilton\HiltonHotelContentDetailTransformer;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Services\SupplierInterface;

class HiltonHotelContentApiService implements SupplierInterface
{
    public function __construct(
        protected readonly HiltonHotelContentDetailTransformer $hiltonHotelContentDetailTransformer,
    ) {}

    public function getResults(array $giataCodes): array
    {
        $hiltonCodes = Mapping::hilton()->whereIn('giata_id', $giataCodes)->pluck('giata_id', 'supplier_id')->toArray();
        $resultsHilton = HiltonProperty::whereIn('prop_code', array_keys($hiltonCodes))->get();

        $results = [];
        foreach ($resultsHilton as $item) {
            $giataId = $hiltonCodes[$item->prop_code];
            $contentDetailResponse = $this->hiltonHotelContentDetailTransformer->HiltonToContentDetailResponse($item, $giataId);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];
        $hiltonCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HILTON->value)
            ->first()?->supplier_id;
        $hiltonData = HiltonProperty::where('prop_code', $hiltonCode)->first();
        $hiltonData = $hiltonData ? $hiltonData->toArray() : [];

        // Example transformation, adjust according to your IcePortalPropertyAsset structure
        $rooms = $hiltonData['guest_room_descriptions'] ?? [];

        foreach ($rooms as $room) {
            $roomId = $room['roomTypeCode'] ?? null;
            $roomsData[] = [
                'id' => $roomId,
                'name' => $room['shortDescription'],
                'descriptions' => $room['enhancedDescription'],
                'area' => $room['area'] ?? null,
                'views' => '',
                'bed_groups' => $room['bedClass'] ?? [],
                'amenities' => $room['roomAmenities'] ?? [],
                'supplier' => SupplierNameEnum::HILTON->value,
                'roomsOccupancy' => $room['maxOccupancy'],
            ];
        }

        return $roomsData;
    }
}
