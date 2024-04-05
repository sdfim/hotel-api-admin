<?php

namespace Modules\API\Suppliers\HbsiSupplier;

use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponse;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class HbsiService
{
    private int $supplier_id;
    private string $rate_type;

    public function __construct()
    {
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $this->rate_type = ItemTypeEnum::SINGLE->value;
    }

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
            $completeBookingItem['booking_pricing_data']['total_price']  += $booking_pricing_data['total_price'];
            if (!isset($completeBookingItem['booking_pricing_data']['total_tax'])) $completeBookingItem['booking_pricing_data']['total_tax'] = 0;
            $completeBookingItem['booking_pricing_data']['total_tax'] += $booking_pricing_data['total_tax'];
            if (!isset($completeBookingItem['booking_pricing_data']['total_fees'])) $completeBookingItem['booking_pricing_data']['total_fees'] = 0;
            $completeBookingItem['booking_pricing_data']['total_fees'] += $booking_pricing_data['total_fees'];
            if (!isset($completeBookingItem['booking_pricing_data']['total_net'])) $completeBookingItem['booking_pricing_data']['total_net'] = 0;
            $completeBookingItem['booking_pricing_data']['total_net'] += $booking_pricing_data['total_net'];
            if (!isset($completeBookingItem['booking_pricing_data']['affiliate_service_charge'])) $completeBookingItem['booking_pricing_data']['affiliate_service_charge'] = 0;
            $completeBookingItem['booking_pricing_data']['affiliate_service_charge'] += $booking_pricing_data['affiliate_service_charge'];
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

        ApiBookingItem::insert($completeBookingItem);

        foreach ($room_combinations as $key => $value) {
            $bookingItem = ApiBookingItem::where('booking_item', $value)->first();
            $bookingItem->complete_id = $completeItem;
            $bookingItem->update();
        }
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

    private function generateSets($arrays)
    {
        $numArrays = count($arrays);
        $sets = [];

        if ($numArrays === 0) {
            return [];
        }

        $maxSize = max(array_map('count', $arrays));

        for ($i = 1; $i <= $maxSize; $i++) {
            if ($i <= $numArrays) {
                $combinations = $this->combinations($arrays, $i);
                foreach ($combinations as $combination) {
                    sort($combination);
                    if ($this->isUniqueCombination($combination)) {
                        $sets[join(',', $combination)] = $combination;
                    }
                }
            }
        }

        return array_values($sets);
    }

    private function isUniqueCombination($combination)
    {
        $uniqueElements = array_unique($combination);
        return count($combination) === count($uniqueElements);
    }

    private function combinations($arrays, $n)
    {
        $result = [];
        $arraysCount = count($arrays);
        $indices = array_fill(0, $arraysCount, 0);

        while (true) {
            $combination = [];
            for ($i = 0; $i < $arraysCount; $i++) {
                $combination = array_merge($combination, [$arrays[$i][$indices[$i]]]);
            }
            $result[] = $combination;

            for ($i = $arraysCount - 1; $i >= 0; $i--) {
                $indices[$i]++;
                if ($indices[$i] < count($arrays[$i])) {
                    break;
                }
                $indices[$i] = 0;
            }

            if (array_sum($indices) === 0) {
                break;
            }
        }

        return $result;
    }

    public function enrichmentRoomCombinations(array $input, array $filters, string $search_id): array
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
                $sets = $this->generateSets(array_values($arr2combine));
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

    public function mergerItems(array $input, array $singlBookingItems, int $countRooms): array
    {
        $result = $input;
        $booking_items = $dataBooking = $keyBookingIitem = [];
        foreach ($input as $hk => $hotel) {
            $minHotelPrice = 100000;
            /** loop room type  (Suite, Double, etc)*/
            foreach ($hotel['room_groups'] as $rgk => $room_groups) {
                /** loop rate type  (Promo, BAR, etc)*/
                $occupancy = $unionRooms = $rateId = [];
                $total_price = $total_tax = $total_fees = $total_net = $affiliate_service_charge = [];
                foreach ($room_groups['rooms'] as $rk => $rooms) {
                    $rate = $rooms['rate_plan_code'];
                    $occupancy[$rate][] = $rooms['supplier_room_id'];
                    $rateId[$rate][] = $rooms['rate_id'];

                    $total_price[$rate] = ($total_price[$rate] ?? 0) + round($rooms['total_price'], 2);
                    $total_tax[$rate] = ($total_tax[$rate] ?? 0) + round($rooms['total_tax'], 2);
                    $total_fees[$rate] = ($total_fees[$rate] ?? 0) + round($rooms['total_fees'], 2);
                    $total_net[$rate] = ($total_net[$rate] ?? 0) + round($rooms['total_net'], 2);
                    $affiliate_service_charge[$rate] = ($affiliate_service_charge[$rate] ?? 0) + round($rooms['affiliate_service_charge'], 2);

                    $item = $hotel['giata_hotel_id'] . '_' . $rooms['supplier_room_name'] . '_' . $rate;
                    $search_result = array_search($item, array_column($keyBookingIitem, 'key'));
                    if ($search_result !== false) {
                        $booking_item = $keyBookingIitem[$search_result]['booking_item'];
                    } else {
                        $booking_item = (string)Str::uuid();
                        $keyBookingIitem[] = [
                            'key' => $item,
                            'booking_item' => $booking_item,
                        ];
                    }
                    $booking_items[$booking_item][] = $rooms['booking_item'];

                    $unionRooms[$rate] = $rooms;
                    $unionRooms[$rate]['total_price'] = round($total_price[$rate], 2);
                    $unionRooms[$rate]['total_tax'] = round($total_tax[$rate], 2);
                    $unionRooms[$rate]['total_fees'] = round($total_fees[$rate], 2);
                    $unionRooms[$rate]['total_net'] = round($total_net[$rate], 2);
                    $unionRooms[$rate]['affiliate_service_charge'] = round($affiliate_service_charge[$rate], 2);
                    $unionRooms[$rate]['supplier_room_id'] = implode(';', $occupancy[$rate]);
                    $unionRooms[$rate]['rate_id'] = implode(';', $rateId[$rate]);
                    $unionRooms[$rate]['booking_item'] = $booking_item;

                    $dataBooking[$booking_item] = $unionRooms[$rate];
                    $dataBooking[$booking_item]['hotel_id'] = $hotel['giata_hotel_id'];
                    $dataBooking[$booking_item]['hotel_supplier_id'] = $hotel['supplier_hotel_id'];
                }
                foreach ($unionRooms as $rate => $room) {
                    if (count(explode(';', $room['supplier_room_id'])) < $countRooms) unset($unionRooms[$rate]);
                }
                $result[$hk]['room_groups'][$rgk]['rooms'] = array_values($unionRooms);
                $total_prices = array_column($result[$hk]['room_groups'][$rgk]['rooms'], 'total_price');
                $min_price = !empty($total_prices) ? min($total_prices) : 0;
                $min_rate_keys = array_keys($total_price, $min_price);
                if (count($min_rate_keys) == 0) {
                    unset($result[$hk]['room_groups'][$rgk]);
                    continue;
                }
                $minGroupePrice = $unionRooms[$min_rate_keys[0]];
                $result[$hk]['room_groups'][$rgk]['total_price'] = round($minGroupePrice['total_price'], 2);
                $result[$hk]['room_groups'][$rgk]['total_tax'] = round($minGroupePrice['total_tax'], 2);
                $result[$hk]['room_groups'][$rgk]['total_fees'] = round($minGroupePrice['total_fees'], 2);
                $result[$hk]['room_groups'][$rgk]['total_net'] = round($minGroupePrice['total_net'], 2);
                $result[$hk]['room_groups'][$rgk]['affiliate_service_charge'] = round($minGroupePrice['affiliate_service_charge'], 2);
                if ($minHotelPrice > $minGroupePrice['total_price']) {
                    $minHotelPrice = round($minGroupePrice['total_price'], 2);
                }
            }
            $result[$hk]['lowest_priced_room_group'] = $minHotelPrice;

            if (empty($result[$hk]['room_groups'])) unset($result[$hk]);
        }

        foreach ($booking_items as $booking_item => $items) {
            if (count($items) < $countRooms) {
                unset($booking_items[$booking_item]);
            }
        }

        if (!$this->validate($result)) return ['error' => 'Invalid response'];

        $booking_items = $this->updateBookingItems($booking_items, $singlBookingItems, $dataBooking);

        return [
            'response' => $result,
            'bookingItems' => $booking_items,
        ];
    }

    private function validate(array $result): bool
    {
        $res = true;
        foreach ($result as $hk => $hotel) {
            $hotelResponse = new HotelResponse();
            if (!$hotelResponse->validateArrayKeys($hotel)) $res = false;
            foreach ($hotel['room_groups'] as $rgk => $room_groups) {
                $roomGroups = new RoomGroupsResponse();
                if (!$roomGroups->validateArrayKeys($room_groups)) $res = false;
                foreach ($room_groups['rooms'] as $rk => $rooms) {
                    $room = new RoomResponse();
                    if (!$room->validateArrayKeys($rooms)) $res = false;
                }
            }
        }
        return $res;
    }

    private function updateBookingItems(array $bookingItems, array $singlBookingItems, array $dataBooking): array
    {
        $result = [];
        foreach ($bookingItems as $booking_item => $items) {
            $bookingItem['booking_item'] = $booking_item;
            $bookingItem['supplier_id'] = $this->supplier_id;
            $bookingItem['search_id'] = $singlBookingItems[0]['search_id'];
            $bookingItem['booking_item_data'] = json_encode([
                'hotel_id' => $dataBooking[$booking_item]['hotel_id'],
                'hotel_supplier_id' => $dataBooking[$booking_item]['hotel_supplier_id'],
                'room_id' => $dataBooking[$booking_item]['supplier_room_name'],
                'rate_ordinal' => $dataBooking[$booking_item]['rate_id'],
                'rate_type' => $this->rate_type,
                'rate_occupancy' => $dataBooking[$booking_item]['supplier_room_id'],
            ]);
            $bookingItem['rate_type'] = $this->rate_type;
            $bookingItem['booking_pricing_data'] = json_encode($dataBooking[$booking_item]);
            $bookingItem['complete_id'] = null;
            $bookingItem['created_at'] = Carbon::now();
            $result[] = $bookingItem;
        }
        foreach ($singlBookingItems as &$singleBookingItem) {
            foreach ($bookingItems as $key => $subArray) {
                if (in_array($singleBookingItem['booking_item'], $subArray)) {
                    $singleBookingItem['complete_id'] = $key;
                    break;
                } else {
                    $singleBookingItem['complete_id'] = null;
                }
            }
        }

        return array_merge($result, $singlBookingItems);

    }

}
