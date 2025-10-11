<?php

namespace Modules\API\Services;

use App\Models\ApiBookingItem;
use App\Models\ApiBookingItemCache;
use App\Models\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class HotelCombinationService
{
    const TTL_CACHE_COMBINATION_ITEMS = 60 * 24;

    private int $supplier_id;

    private string $supplier_name;

    private string $rate_type;

    public function __construct(string $supplierName)
    {
        $this->supplier_id = Supplier::where('name', $supplierName)->first()->id;
        $this->supplier_name = $supplierName;
        $this->rate_type = ItemTypeEnum::SINGLE->value;
    }

    // Update booking items data for hotel combinations
    public function updateBookingItemsData(string $completeItem, bool $isChangeBookFlow = false, array $room_combinations = []): void
    {
        logger()->debug('HotelCombinationService::updateBookingItemsData', [
            'completeItem' => $completeItem,
            'isChangeBookFlow' => $isChangeBookFlow,
            'room_combinations' => $room_combinations,
            'supplier_name' => $this->supplier_name,
            'supplier_id' => $this->supplier_id,
        ]);

        if (empty($room_combinations)) {
            $room_combinations = Cache::get('room_combinations:'.$completeItem);
        }
        $completeBookingItem = [];

        foreach ($room_combinations as $key => $value) {
            $bookingItem = null;
            $waitTime = 0;
            $maxWaitTime = 5;

            while ($waitTime < $maxWaitTime) {
                $bookingItem = ApiBookingItemCache::where('booking_item', $value)->first();
                if ($bookingItem) {
                    break;
                }
                sleep(1);
                $waitTime++;
            }

//            $isChangeBookFlow ?: ApiBookingItem::insertOrIgnore($bookingItem->toArray());

            ApiBookingItem::insertOrIgnore($bookingItem->toArray());

            $booking_item_data = json_decode($bookingItem->booking_item_data, true);
            $booking_pricing_data = json_decode($bookingItem->booking_pricing_data, true);

            $search_id = $bookingItem->search_id;

            $completeBookingItem['booking_item_data']['room_id'][] = $booking_item_data['room_id'];
            $completeBookingItem['booking_item_data']['hotel_name'] = $booking_item_data['hotel_name'];
            $completeBookingItem['booking_item_data']['room_code'][] = $booking_item_data['room_code'];
            $completeBookingItem['booking_item_data']['rate_code'][] = $booking_item_data['rate_code'];
            $completeBookingItem['booking_item_data']['rate_ordinal'][] = $booking_item_data['rate_ordinal'];
            $completeBookingItem['booking_item_data']['rate_type'] = ItemTypeEnum::COMPLETE->value;
            $completeBookingItem['booking_item_data']['rate_occupancy'][] = $booking_item_data['rate_occupancy'];
            $completeBookingItem['booking_item_data']['hotel_supplier_id'] = $booking_item_data['hotel_supplier_id'];
            $completeBookingItem['booking_item_data']['hotel_id'] = $booking_item_data['hotel_id'];

            if ($this->supplier_name === SupplierNameEnum::HOTEL_TRADER->value) {
                $completeBookingItem['booking_item_data']['htIdentifier'][] = $booking_item_data['htIdentifier'];
                $completeBookingItem['booking_item_data']['rate'][] = $booking_item_data['rate'];
                logger()->debug('Hotel Trader booking_item_data', $completeBookingItem['booking_item_data']);
            }

            if (! isset($completeBookingItem['booking_pricing_data']['total_price'])) {
                $completeBookingItem['booking_pricing_data']['total_price'] = 0;
            }
            $completeBookingItem['booking_pricing_data']['total_price'] += $booking_pricing_data['total_price'];
            if (! isset($completeBookingItem['booking_pricing_data']['total_tax'])) {
                $completeBookingItem['booking_pricing_data']['total_tax'] = 0;
            }
            $completeBookingItem['booking_pricing_data']['total_tax'] += $booking_pricing_data['total_tax'];
            if (! isset($completeBookingItem['booking_pricing_data']['total_fees'])) {
                $completeBookingItem['booking_pricing_data']['total_fees'] = 0;
            }
            $completeBookingItem['booking_pricing_data']['total_fees'] += $booking_pricing_data['total_fees'];
            if (! isset($completeBookingItem['booking_pricing_data']['total_net'])) {
                $completeBookingItem['booking_pricing_data']['total_net'] = 0;
            }
            $completeBookingItem['booking_pricing_data']['total_net'] += $booking_pricing_data['total_net'];
            if (! isset($completeBookingItem['booking_pricing_data']['markup'])) {
                $completeBookingItem['booking_pricing_data']['markup'] = 0;
            }
            $completeBookingItem['booking_pricing_data']['markup'] += Arr::get($booking_pricing_data, 'markup', 0);
            $completeBookingItem['booking_pricing_data']['rate_id'][] = Arr::get($booking_pricing_data, 'rate_id');
            $completeBookingItem['booking_pricing_data']['currency'] = Arr::get($booking_pricing_data, 'currency');
            $completeBookingItem['booking_pricing_data']['meal_plan'][] = Arr::get($booking_pricing_data, 'meal_plan');
            $completeBookingItem['booking_pricing_data']['room_type'][] = Arr::get($booking_pricing_data, 'room_type');
            $completeBookingItem['booking_pricing_data']['non_refundable'] = Arr::get($booking_pricing_data, 'non_refundable');
            $completeBookingItem['booking_pricing_data']['rate_plan_code'][] = Arr::get($booking_pricing_data, 'rate_plan_code');
            $completeBookingItem['booking_pricing_data']['giata_room_code'][] = Arr::get($booking_pricing_data, 'giata_room_code');
            $completeBookingItem['booking_pricing_data']['giata_room_name'][] = Arr::get($booking_pricing_data, 'giata_room_name');
            $completeBookingItem['booking_pricing_data']['rate_description'][] = Arr::get($booking_pricing_data, 'rate_description');
            $completeBookingItem['booking_pricing_data']['supplier_room_id'][] = Arr::get($booking_pricing_data, 'supplier_room_id');
            $completeBookingItem['booking_pricing_data']['supplier_room_name'][] = Arr::get($booking_pricing_data, 'supplier_room_name');

            $completeBookingItem['booking_pricing_data']['cancellation_policies'][] = [
                'room' => ($key + 1),
                'booking_item' => $bookingItem->booking_item,
                'cancellation_policies' => $booking_pricing_data['cancellation_policies'],
            ];
            $completeBookingItem['booking_pricing_data']['breakdown'][] = [
                'room' => ($key + 1),
                'booking_item' => $bookingItem->booking_item,
                'breakdown' => $booking_pricing_data['breakdown'],
            ];
        }

        $completeBookingItem['booking_item'] = $completeItem;
        $completeBookingItem['supplier_id'] = $this->supplier_id;
        $completeBookingItem['search_id'] = $search_id;
        $completeBookingItem['rate_type'] = ItemTypeEnum::COMPLETE->value;

        $completeBookingItem['booking_item_data']['room_id'] = implode(';', $completeBookingItem['booking_item_data']['room_id']);
        $completeBookingItem['booking_item_data']['room_code'] = implode(';', $completeBookingItem['booking_item_data']['room_code']);
        $completeBookingItem['booking_item_data']['rate_code'] = implode(';', $completeBookingItem['booking_item_data']['rate_code']);
        $completeBookingItem['booking_item_data']['rate_ordinal'] = implode(';', $completeBookingItem['booking_item_data']['rate_ordinal']);
        $completeBookingItem['booking_item_data']['rate_occupancy'] = implode(';', $completeBookingItem['booking_item_data']['rate_occupancy']);

        if ($this->supplier_name === SupplierNameEnum::HOTEL_TRADER->value) {
            $completeBookingItem['booking_item_data']['htIdentifier'] = implode(';', $completeBookingItem['booking_item_data']['htIdentifier']);
            $completeBookingItem['booking_item_data']['rate'] = json_decode(json_encode($completeBookingItem['booking_item_data']['rate']));
        }

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
        $completeBookingItem['booking_pricing_data']['cancellation_policies'] = json_decode(json_encode($completeBookingItem['booking_pricing_data']['cancellation_policies']));
        $completeBookingItem['booking_pricing_data']['breakdown'] = json_decode(json_encode($completeBookingItem['booking_pricing_data']['breakdown']));
        $completeBookingItem['booking_pricing_data'] = json_encode($completeBookingItem['booking_pricing_data']);

        $bookingItemModel = match ($isChangeBookFlow) {
            true => ApiBookingItem::class,
            false => ApiBookingItemCache::class,
        };
        $bookingItemModel::insertOrIgnore($completeBookingItem);
        $bookingParentItem = $bookingItemModel::where('booking_item', $completeItem)->first();
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
            if (isset($value['children_ages']) && ! empty($value['children_ages'])) {
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
        if (! isset($arrays[$i])) {
            return [];
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }
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
        // Determine supplier name by id
        if ($this->supplier_name === SupplierNameEnum::HBSI->value) {
            // HBSI logic (existing)
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
                        $uuid = (string) Str::uuid();
                        $finalResult[$uuid] = $set;
                    }
                    $input[$hk]['room_combinations'] = $finalResult;
                    foreach ($finalResult as $key => $value) {
                        $keyCache = 'room_combinations:'.$key;
                        Cache::put($keyCache, $value, now()->addMinutes(self::TTL_CACHE_COMBINATION_ITEMS));
                        Cache::put('supplier:'.$key, SupplierNameEnum::HBSI->value, now()->addMinutes(self::TTL_CACHE_COMBINATION_ITEMS));
                    }
                }
            }

            return $input;
        }
        // HotelTrader logic
        if ($this->supplier_name === SupplierNameEnum::HOTEL_TRADER->value) {
            foreach ($input as $hk => $hotel) {
                $allRooms = [];
                foreach ($hotel['room_groups'] as $roomGroup) {
                    foreach ($roomGroup['rooms'] as $room) {
                        $allRooms[] = $room;
                    }
                }
                // Группируем booking_item по supplier_room_id
                $bySupplierRoomId = [];
                foreach ($allRooms as $room) {
                    $bySupplierRoomId[$room['supplier_room_id']][] = $room['booking_item'];
                }
                // Берём только те supplier_room_id, которых достаточно для бронирования
                ksort($bySupplierRoomId, SORT_NUMERIC);
                $supplierRoomIds = array_keys($bySupplierRoomId);
                $countRooms = count($filters['occupancy']);
                $finalResult = [];
                // Перебираем все подмножества supplier_room_id длиной countRooms, строго по возрастанию
                $supplierRoomIdCombinations = $this->getCombinations($supplierRoomIds, $countRooms);
                foreach ($supplierRoomIdCombinations as $idCombo) {
                    // Для каждого supplier_room_id выбираем booking_item (декартово произведение)
                    $itemsGroups = [];
                    foreach ($idCombo as $id) {
                        $itemsGroups[] = $bySupplierRoomId[$id];
                    }
                    foreach ($this->cartesianProduct($itemsGroups) as $bookingItemsCombo) {
                        $uuid = (string) Str::uuid();
                        $finalResult[$uuid] = $bookingItemsCombo;
                        $keyCache = 'room_combinations:'.$uuid;
                        Cache::put($keyCache, $bookingItemsCombo, now()->addMinutes(self::TTL_CACHE_COMBINATION_ITEMS));
                        Cache::put('supplier:'.$uuid, SupplierNameEnum::HOTEL_TRADER->value, now()->addMinutes(self::TTL_CACHE_COMBINATION_ITEMS));
                        logger()->debug('HotelCombinationService _ enrichmentRoomCombinations', [
                            'keyCache' => $keyCache,
                            'bookingItemsCombo' => $bookingItemsCombo,
                        ]);
                    }
                }
                $input[$hk]['room_combinations'] = $finalResult;
            }

            return $input;
        }

        // Default: return input unchanged
        return $input;
    }

    /**
     * Get all combinations of a given size k from array arr
     */
    private function getCombinations(array $arr, int $k): array
    {
        $results = [];
        $this->combineRecursive($arr, $k, 0, [], $results);

        return $results;
    }

    private function combineRecursive(array $arr, int $k, int $start, array $path, array &$results)
    {
        if (count($path) === $k) {
            $results[] = $path;

            return;
        }
        for ($i = $start; $i < count($arr); $i++) {
            $this->combineRecursive($arr, $k, $i + 1, array_merge($path, [$arr[$i]]), $results);
        }
    }

    /**
     * Декартово произведение массивов
     */
    private function cartesianProduct($arrays)
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property_value]);
                }
            }
            $result = $tmp;
        }

        return $result;
    }
}
