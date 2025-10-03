<?php

namespace App\Repositories;

use App\Models\ApiBookingItem;
use App\Models\ApiBookingItemCache;
use Illuminate\Support\Arr;
use Modules\Enums\ItemTypeEnum;

class ApiBookingItemRepository
{
    public static function isComleteCache(string $booking_item): bool
    {
        $bookingItem = ApiBookingItemCache::where('booking_item', $booking_item)->first();
        if (! $bookingItem) {
            return false;
        }

        return $bookingItem->rate_type === ItemTypeEnum::COMPLETE->value;
    }

    public static function getItemDataCache(string $booking_item): ?array
    {
        $bookingItem = ApiBookingItemCache::where('booking_item', $booking_item)->first();

        return json_decode($bookingItem?->booking_item_data, true);
    }

    public static function isComlete(string $booking_item): bool
    {
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();

        return $bookingItem->rate_type === ItemTypeEnum::COMPLETE->value;
    }

    public static function isHas(string $booking_item): bool
    {
        return (bool) ApiBookingItem::where('booking_item', $booking_item);
    }

    public static function getSearchId(string $booking_item): string
    {
        return ApiBookingItem::where('booking_item', $booking_item)->first()?->search_id ?? '';
    }

    public static function getItemData(string $booking_item): ?array
    {
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();

        return json_decode($bookingItem?->booking_item_data, true);
    }

    public static function getItemPricingData(string $booking_item): ?array
    {
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();

        return json_decode($bookingItem?->booking_pricing_data, true);
    }

    public static function getPricingData(string $booking_item): ?array
    {
        $pricingData = [];
        $childList = self::getChildrenBookingItems($booking_item);

        if (! $childList) {
            $itemPricedData = self::getItemPricingData($booking_item);
            if ($itemPricedData) {
                $pricingData = [
                    'total_net' => $itemPricedData['total_net'] ?? 0,
                    'total_tax' => $itemPricedData['total_tax'] ?? 0,
                    'total_fees' => $itemPricedData['total_fees'] ?? 0,
                    'total_price' => $itemPricedData['total_price'] ?? 0,
                    'currency' => $itemPricedData['currency'] ?? 'USD',
                ];
            }

            return $pricingData;
        }

        foreach ($childList as $child) {
            $itemPricedData = self::getItemPricingData($child);
            if ($itemPricedData) {
                $pricingData['total_net'] = ($pricingData['total_net'] ?? 0) + ($itemPricedData['total_net'] ?? 0);
                $pricingData['total_tax'] = ($pricingData['total_tax'] ?? 0) + ($itemPricedData['total_tax'] ?? 0);
                $pricingData['total_fees'] = ($pricingData['total_fees'] ?? 0) + ($itemPricedData['total_fees'] ?? 0);
                $pricingData['total_price'] = ($pricingData['total_price'] ?? 0) + ($itemPricedData['total_price'] ?? 0);
                $pricingData['currency'] = $itemPricedData['currency'] ?? 'USD';
            }
        }

        return $pricingData;
    }

    public static function isNonRefundable(string $booking_item): bool
    {
        $res = false;
        $childList = self::getChildrenBookingItems($booking_item);
        if (! $childList) {
            $childList = [$booking_item];
        }
        foreach ($childList as $child) {
            $item = self::getItemPricingData($child);
            $currentRes = Arr::get($item, 'non_refundable', false);
            if ($currentRes) {
                $res = true;
                break;
            }
        }

        return $res;
    }

    public static function getRateOccupancy(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['rate_occupancy'] ?? null;
    }

    public static function getRateType(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['rate_type'];
    }

    public static function getRoomId(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['room_id'];
    }

    public static function getHotelId(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['hotel_id'];
    }

    public static function getBedGroups(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['bed_groups'];
    }

    public static function getRateOrdinal(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['rate_ordinal'];
    }

    public static function getHotelSupplierId(string $booking_item): ?string
    {
        return Arr::get(self::getItemData($booking_item), 'hotel_supplier_id');
    }

    public static function getChildrenBookingItems(string $bookingItem): ?array
    {
        return ApiBookingItem::where('booking_item', $bookingItem)->first()?->child_items;
    }

    public static function getListQuoteByBookingItems(array $listBookingItems): ?array
    {
        $order = [
            'unified_room_code',
            'room_type', 'rate_plan_code', 'rate_name', 'supplier_room_name',
            'booking_item', 'non_refundable',
            'currency', 'total_net', 'total_tax', 'total_price', 'total_fees',  'commissionable_amount', 'markup',
            'breakdown', 'cancellation_policies',
            'capacity', 'rate_description', 'room_description',
            'penalty_date',  'meal_plan', 'amenities', 'promotions', 'rate_id',
            'distribution', 'package_deal',
            'query_package', 'giata_room_code', 'giata_room_name', 'supplier_room_id',
            'bed_configurations', 'descriptive_content', 'pricing_rules_applier', 'per_day_rate_breakdown', 'deposits',
        ];
        $reorder = function (array $data) use ($order) {
            $result = [];
            foreach ($order as $key) {
                if (array_key_exists($key, $data)) {
                    $result[$key] = $data[$key];
                }
            }
            foreach ($data as $key => $value) {
                if (! array_key_exists($key, $result)) {
                    $result[$key] = $value;
                }
            }

            return $result;
        };

        $items = ApiBookingItem::whereIn('booking_item', $listBookingItems)->get();

        $detailItems = [];
        foreach ($items as $i => $item) {
            $detailItems[$i]['booking_id'] = ApiBookingInspectorRepository::getBookingIdByQuote($item->booking_item);
            $detailItems[$i]['booking_item'] = $item->booking_item;
            $detailItems[$i]['email_verified'] = (bool) $item->email_verified;
            $childItems = $item->child_items;
            if ($childItems && is_array($childItems)) {
                foreach ($childItems as $r => $childItem) {
                    $childPricingData = self::getItemPricingData($item->booking_item);
                    $room = 'room '.($r + 1);
                    $roomNumber = $r + 1; // current room number

                    // Filter breakdown for this room and extract the breakdown object
                    $breakdownObj = null;
                    if (! empty($childPricingData['breakdown'])) {
                        foreach ($childPricingData['breakdown'] as $break) {
                            if (isset($break['room']) && $break['room'] == $roomNumber) {
                                $breakdownObj = $break['breakdown'] ?? null;
                                break;
                            }
                        }
                    }
                    if ($breakdownObj !== null) {
                        $childPricingData['breakdown'] = $breakdownObj;
                    }
                    // Filter cancellation_policies for this room and extract the cancellation_policies array
                    $cancellationPoliciesObj = null;
                    if (! empty($childPricingData['cancellation_policies'])) {
                        foreach ($childPricingData['cancellation_policies'] as $policy) {
                            if (isset($policy['room']) && $policy['room'] == $roomNumber) {
                                $cancellationPoliciesObj = $policy['cancellation_policies'] ?? null;
                                break;
                            }
                        }
                    }
                    if ($cancellationPoliciesObj !== null) {
                        $childPricingData['cancellation_policies'] = $cancellationPoliciesObj;
                    }

                    $childPricingData['booking_item'] = $childItem;

                    // Remove room key from breakdown items
                    $detailItems[$i]['rooms'][] = array_merge(['room' => $room], $reorder($childPricingData));
                }
            } else {
                $room = 'room 1';
                $detailItems[$i]['rooms'][] = array_merge(['room' => $room], $reorder(json_decode($item->booking_pricing_data, true)));
            }
        }

        return $detailItems;
    }
}
