<?php

namespace Modules\API\Suppliers\HbsiSupplier;

use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class HbsiService
{
    public function updateBookingItemsData(string $completeItem): void
    {
        $room_combinations = Cache::get('room_combinations:' . $completeItem);
        $completeBookingItem = [];
        foreach ($room_combinations as $key => $value) {
            $bookingItem = ApiBookingItem::where('booking_item', $value)->first();
            $booking_item_data = json_decode($bookingItem->booking_item_data, true);
            $booking_pricing_data = json_decode($bookingItem->booking_pricing_data, true);

            $search_id = $bookingItem->search_id;

            $completeBookingItem['booking_item_data']['room_id'][] = $booking_item_data['room_id'];
            $completeBookingItem['booking_item_data']['rate_ordinal'][] = $booking_item_data['rate_ordinal'];
            $completeBookingItem['booking_item_data']['rate_type'] = ItemTypeEnum::COMPLETE->value;
            $completeBookingItem['booking_item_data']['rate_occupancy'][] = $booking_item_data['rate_occupancy'];
            $completeBookingItem['booking_item_data']['hotel_supplier_id'] = $booking_item_data['hotel_supplier_id'];
            $completeBookingItem['booking_item_data']['hotel_id'] = $booking_item_data['hotel_id'];

            if (!isset($completeBookingItem['booking_pricing_data']['total_price'])) $completeBookingItem['booking_pricing_data']['total_price'] = 0;
            $completeBookingItem['booking_pricing_data']['total_price'] += $booking_pricing_data['total_price'];
            if (!isset($completeBookingItem['booking_pricing_data']['total_tax'])) $completeBookingItem['booking_pricing_data']['total_tax'] = 0;
            $completeBookingItem['booking_pricing_data']['total_tax'] += $booking_pricing_data['total_tax'];
            if (!isset($completeBookingItem['booking_pricing_data']['total_fees'])) $completeBookingItem['booking_pricing_data']['total_fees'] = 0;
            $completeBookingItem['booking_pricing_data']['total_fees'] += $booking_pricing_data['total_fees'];
            if (!isset($completeBookingItem['booking_pricing_data']['total_net'])) $completeBookingItem['booking_pricing_data']['total_net'] = 0;
            $completeBookingItem['booking_pricing_data']['total_net'] += $booking_pricing_data['total_net'];
            if (!isset($completeBookingItem['booking_pricing_data']['markup'])) $completeBookingItem['booking_pricing_data']['markup'] = 0;
            $completeBookingItem['booking_pricing_data']['markup'] += $booking_pricing_data['markup'];
            $completeBookingItem['booking_pricing_data']['rate_id'][] = $booking_pricing_data['rate_id'];
            $completeBookingItem['booking_pricing_data']['currency'] = $booking_pricing_data['currency'];
            $completeBookingItem['booking_pricing_data']['meal_plan'][] = $booking_pricing_data['meal_plan'];
            $completeBookingItem['booking_pricing_data']['room_type'][] = $booking_pricing_data['room_type'];
            $completeBookingItem['booking_pricing_data']['non_refundable'] = $booking_pricing_data['non_refundable'];
            $completeBookingItem['booking_pricing_data']['rate_plan_code'][] = $booking_pricing_data['rate_plan_code'];
            $completeBookingItem['booking_pricing_data']['giata_room_code'][] = $booking_pricing_data['giata_room_code'];
            $completeBookingItem['booking_pricing_data']['giata_room_name'][] = $booking_pricing_data['giata_room_name'];
            $completeBookingItem['booking_pricing_data']['rate_description'][] = $booking_pricing_data['rate_description'];
            $completeBookingItem['booking_pricing_data']['supplier_room_id'][] = $booking_pricing_data['supplier_room_id'];
            $completeBookingItem['booking_pricing_data']['supplier_room_name'][] = $booking_pricing_data['supplier_room_name'];
        }


        $completeBookingItem['booking_item'] = $completeItem;
        $completeBookingItem['supplier_id'] = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $completeBookingItem['search_id'] = $search_id;
        $completeBookingItem['rate_type'] = ItemTypeEnum::COMPLETE->value;

        $completeBookingItem['booking_item_data']['room_id'] = implode(';', $completeBookingItem['booking_item_data']['room_id']);
        $completeBookingItem['booking_item_data']['rate_ordinal'] = implode(';', $completeBookingItem['booking_item_data']['rate_ordinal']);
        $completeBookingItem['booking_item_data']['rate_occupancy'] = implode(';', $completeBookingItem['booking_item_data']['rate_occupancy']);
        $completeBookingItem['booking_item_data'] = json_encode($completeBookingItem['booking_item_data']);

        $completeBookingItem['booking_pricing_data']['rate_id'] = implode(';', $completeBookingItem['booking_pricing_data']['rate_id']);
        $completeBookingItem['booking_pricing_data']['meal_plan'] = implode(';', $completeBookingItem['booking_pricing_data']['meal_plan']);
        $completeBookingItem['booking_pricing_data']['room_type'] = implode(';', $completeBookingItem['booking_pricing_data']['room_type']);
        $completeBookingItem['booking_pricing_data']['rate_plan_code'] = implode(';', $completeBookingItem['booking_pricing_data']['rate_plan_code']);
        $completeBookingItem['booking_pricing_data']['giata_room_code'] = implode(';', $completeBookingItem['booking_pricing_data']['giata_room_code']);
        $completeBookingItem['booking_pricing_data']['giata_room_name'] = implode(';', $completeBookingItem['booking_pricing_data']['giata_room_name']);
        $completeBookingItem['booking_pricing_data']['rate_description'] = implode(';', $completeBookingItem['booking_pricing_data']['rate_description']);
        $completeBookingItem['booking_pricing_data']['supplier_room_id'] = implode(';', $completeBookingItem['booking_pricing_data']['supplier_room_id']);
        $completeBookingItem['booking_pricing_data']['supplier_room_name'] = implode(';', $completeBookingItem['booking_pricing_data']['supplier_room_name']);
        $completeBookingItem['booking_pricing_data'] = json_encode($completeBookingItem['booking_pricing_data']);

        ApiBookingItem::insertOrIgnore($completeBookingItem);

        $bookingParentItem = ApiBookingItem::where('booking_item', $completeItem)->first();
        $bookingParentItem->child_items = $room_combinations;
        $bookingParentItem->update();
    }


    public function getArrOccupancy(array $filters): array
    {
        $arrayOccupancy = [];
        foreach ($filters['occupancy'] as $key => $value) {
            $adults = $value['adults'];
            $child = 0;
            $infant = 0;
            if (isset($value['children_ages']) && !empty($value['children_ages'])) {
                foreach ($value['children_ages'] as $kid => $childAge) {
                    if ($childAge <= 2) {
                        $infant++;
                    } else {
                        $child++;
                    }
                }
            }
            $arrayOccupancy[] = "$adults-$child-$infant";
        }

        return $arrayOccupancy;
    }

    private function generateCombinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) return [];
        if ($i == count($arrays) - 1) return $arrays[$i];
        $tmp = $this->generateCombinations($arrays, $i + 1);
        $result = [];
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge([$v], $t) :
                    [$v, $t];
            }
        }

        return $result;
    }

    public function enrichmentRoomCombinations(array $input, array $filters): array
    {
        $arrayOccupancy = $this->getArrOccupancy($filters);
        foreach ($input as $hk => $hotel) {
            $result = $arr2combine = [];
            /** loop room type  (Suite, Double, etc)*/
            foreach ($hotel['room_groups'] as $rgk => $room_groups) {
                /** loop rate type  (Promo, BAR, etc)*/

                foreach ($room_groups['rooms'] as $rk => $room) {
                    if (in_array($room['supplier_room_id'], $arrayOccupancy)) {
                        $result[$room['supplier_room_id']][] = $room['booking_item'];
                    }
                }
            }
            foreach ($arrayOccupancy as $occupancy) {
                if (isset($result[$occupancy])) {
                    $arr2combine[] = $result[$occupancy];
                }
            }
            if (count($arr2combine) === count($arrayOccupancy)) {
                $sets = $this->generateCombinations(array_values($arr2combine));
                $finalResult = [];
                foreach ($sets as $set) {
                    $uuid = (string)Str::uuid();
                    $finalResult[$uuid] = $set;
                }
                $input[$hk]['room_combinations'] = $finalResult;
                foreach ($finalResult as $key => $value) {
                    $keyCache = 'room_combinations:' . $key;
                    Cache::put($keyCache, $value, now()->addMinutes(120));
                }
            }
        }

        return $input;
    }

}
