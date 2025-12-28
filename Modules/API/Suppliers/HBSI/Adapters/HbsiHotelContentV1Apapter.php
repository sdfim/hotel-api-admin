<?php

namespace Modules\API\Suppliers\HBSI\Adapters;

use App\Models\HbsiProperty;
use App\Models\Mapping;
use Illuminate\Support\Arr;
use Modules\API\Suppliers\Contracts\Hotel\ContentV1\HotelContentV1SupplierInterface;
use Modules\Enums\SupplierNameEnum;

class HbsiHotelContentV1Apapter implements HotelContentV1SupplierInterface
{
    public function __construct() {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::HBSI;
    }

    public function getResults(array $giataCodes): array {}

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];
        $hbsiCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HBSI->value)
            ->first()?->supplier_id;

        $hbsiData = HbsiProperty::where('hotel_code', $hbsiCode)->first();
        $hbsiData = $hbsiData ? $hbsiData->toArray() : [];

        $mappingRooms = Arr::get($hbsiData, 'tpa_extensions.InterfaceSetup', []);
        $mapping = [];
        foreach ($mappingRooms as $room) {
            if (isset($room['key']) && $room['key'] === 'Mapping_Roomtype') {
                $mapping[$room['value']] = $room['text'];
            }
        }
        $roomTypes = Arr::get($hbsiData, 'roomtypes', []);

        foreach ($roomTypes as $room) {
            $description = '';
            if (isset($room['details']) && is_array($room['details'])) {
                foreach ($room['details'] as $detail) {
                    if (isset($detail['key']) && $detail['key'] === 'Description_ENG') {
                        $description = $detail['value'];
                        break;
                    }
                }
            }
            $roomsData[] = [
                'id' => $mapping[$room['key']] ?? '',
                'name' => $description,
                'descriptions' => $description,
            ];
        }

        return $roomsData;
    }

    public function getTaxOptions(int $giataCode): array
    {
        $hbsiCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HBSI->value)
            ->first()?->supplier_id;

        $hbsiData = HbsiProperty::where('hotel_code', $hbsiCode)->first();
        $hbsiData = $hbsiData ? $hbsiData->toArray() : [];

        $taxOptions = Arr::get($hbsiData, 'tpa_extensions.Taxes', []);

        return array_values(Arr::pluck($taxOptions, 'key'));
    }
}
