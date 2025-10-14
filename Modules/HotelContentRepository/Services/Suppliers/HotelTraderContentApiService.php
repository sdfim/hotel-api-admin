<?php

namespace Modules\HotelContentRepository\Services\Suppliers;

use App\Models\HotelTraderProperty;
use App\Models\Mapping;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderContentDetailTransformer;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Services\SupplierInterface;

class HotelTraderContentApiService implements SupplierInterface
{
    public function __construct(
        protected readonly HotelTraderContentDetailTransformer $hotelTraderContentDetailTransformer
    ) {}

    public function getResults(array $giataCodes): array
    {
        $hotelTraderCodes = Mapping::hotelTrader()->whereIn('giata_id', $giataCodes)->pluck('giata_id', 'supplier_id')->toArray();
        $resultsHotelTrader = HotelTraderProperty::whereIn('propertyId', array_keys($hotelTraderCodes))->get();

        $results = [];
        foreach ($resultsHotelTrader as $item) {
            $giataId = $hotelTraderCodes[$item->propertyId];
            $contentDetailResponse = $this->hotelTraderContentDetailTransformer->HotelTraderToContentDetailResponse($item, $giataId);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];

        $hotelTraderCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HOTEL_TRADER->value)
            ->first()?->supplier_id;
        $hotelTraderData = HotelTraderProperty::where('propertyId', $hotelTraderCode)->first();

        $hotelTraderData = $hotelTraderData ? $hotelTraderData->toArray() : [];
        $rooms = $hotelTraderData['rooms'] ?? [];

        foreach ($rooms as $room) {
            $roomId = $room['roomCode'] ?? $room['displayName'] ?? null;
            $roomsData[] = [
                'id' => $roomId,
                'name' => $room['displayName'] ?? '',
                'descriptions' => [
                    'overview' => $room['shortDesc'] ?? '',
                ],
                'area' => null, // Not provided
                'views' => [], // Not provided
                'bed_groups' => [], // Not provided
                'amenities' => [], // Not provided
                'supplier' => SupplierNameEnum::HOTEL_TRADER->value,
            ];
        }

        return $roomsData;
    }
}
