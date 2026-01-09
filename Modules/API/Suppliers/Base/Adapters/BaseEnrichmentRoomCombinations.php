<?php

namespace Modules\API\Suppliers\Base\Adapters;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;

class BaseEnrichmentRoomCombinations
{
    const TTL_CACHE_COMBINATION_ITEMS = 60 * 24;

    public function enrichmentRoomCombinations(array $input, array $filters, SupplierNameEnum $supplier): array
    {
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
                    Cache::put('supplier:'.$uuid, $supplier->value, now()->addMinutes(self::TTL_CACHE_COMBINATION_ITEMS));
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
