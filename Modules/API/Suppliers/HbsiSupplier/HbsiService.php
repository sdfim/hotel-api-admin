<?php

namespace Modules\API\Suppliers\HbsiSupplier;

use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponse;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\Enums\SupplierNameEnum;

class HbsiService
{
    private int $supplier_id;
    private string $rate_type;

    public function __construct()
    {
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $this->rate_type = 'complete';
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
