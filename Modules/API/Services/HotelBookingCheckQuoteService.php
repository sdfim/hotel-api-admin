<?php

namespace Modules\API\Services;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\API\BaseController;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingSupplierRegistry;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelBookingCheckQuoteService extends BaseController
{
    public function __construct(
        private readonly HotelBookingSupplierRegistry $supplierRegistry,
    ) {}

    public function prepareFiltersForCheckQuote(&$filters, $request, $bookingItem, $firstSearch, $dataFirstSearch)
    {
        $firstQuery = $firstSearch->request;

        $booking_item_data = json_decode($bookingItem->booking_item_data, true);

        $rateIdsSrt = $booking_item_data['room_id'];
        $rateIds = is_array($rateIdsSrt) ? $rateIdsSrt : explode(';', $rateIdsSrt);

        $rateCodesSrt = $booking_item_data['rate_code'];
        $rateCodes = is_array($rateCodesSrt) ? $rateCodesSrt : explode(';', $rateCodesSrt);

        $roomCodesSrt = $booking_item_data['room_code'];
        $roomCodes = is_array($roomCodesSrt) ? $roomCodesSrt : explode(';', $roomCodesSrt);

        $filters = array_merge($filters, json_decode($firstQuery, true));

        if ($request->has('checkin') && $request->has('checkout')) {
            $filters['checkin'] = $request->checkin;
            $filters['checkout'] = $request->checkout;
        }

        if ($request->has('occupancy')) {
            $filters['occupancy'] = $request->occupancy;
        }
        foreach ($filters['occupancy'] as $r => $occupancy) {
            $filters['occupancy'][$r]['rate_code'] = $rateCodes[$r] ?? null;
            $filters['occupancy'][$r]['room_code'] = $roomCodes[$r] ?? null;
            $filters['occupancy'][$r]['room_id'] = $rateIds[$r] ?? null;
        }

        $filters['giata_ids'] = [$dataFirstSearch[0]['giata_code']];
        $filters['supplier'] = [$bookingItem->supplier->name];
    }

    public function getDataFirstSearch(ApiBookingItem $bookingItem)
    {
        $dataFirstSearch = [];
        $ChildrenBookingItems = ApiBookingItemRepository::getChildrenBookingItems($bookingItem->booking_item) ?? [];
        if (count($ChildrenBookingItems) > 0) {
            foreach ($ChildrenBookingItems as $child) {
                $parentBookingItem = $bookingItem;
                $childrenBookingItem = ApiBookingItem::with('supplier')->where('booking_item', $child)->first();
                $dataFirstSearch[] = $this->parseCacheCheckpoint($childrenBookingItem, $parentBookingItem);
            }
        } else {
            $dataFirstSearch[] = $this->parseCacheCheckpoint($bookingItem, $bookingItem);
        }

        return $dataFirstSearch;
    }

    /**
     * hotel_giata_code:room_code:rate_code:supplier
     */
    public function parseCacheCheckpoint($childrenBookingItem, $parentBookingItem)
    {
        $bookingItemData = explode(':', $childrenBookingItem->cache_checkpoint);
        $pricingData = json_decode($childrenBookingItem->booking_pricing_data, true);
        $roomId = Arr::get($pricingData, 'giata_room_code');
        $roomData = HotelRoom::with('galleries.images')->find($roomId);
        $roomImage = null;
        if ($roomData && $roomData->galleries->count()) {
            $url = $roomData->galleries
                ->flatMap(function ($gallery) {
                    return $gallery->images;
                })
                ->pluck('image_url')
                ->filter()
                ->first();
            $roomImage = $url ?: null;
        }

        return [
            'giata_code' => Arr::get($bookingItemData, 0, 0),
            'room_code' => Arr::get($bookingItemData, 1, 0),
            'room_name' => Arr::get($pricingData, 'supplier_room_name', ''),
            //            'rate_code' => Arr::get($bookingItemData, 2, 0),
            'rate_code' => Arr::get($pricingData, 'rate_plan_code', ''),
            'booking_item' => $childrenBookingItem->booking_item,
            'parent_booking_item' => $parentBookingItem->booking_item,
            'total_net' => Arr::get($pricingData, 'total_net', 0),
            'total_tax' => Arr::get($pricingData, 'total_tax', 0),
            'total_fees' => Arr::get($pricingData, 'total_fees', 0),
            'total_price' => Arr::get($pricingData, 'total_price', 0),
            'markup' => Arr::get($pricingData, 'markup', 0),
            'currency' => Arr::get($pricingData, 'currency', 'USD'),
            'supplier_room_id' => Arr::get($pricingData, 'supplier_room_id', 'USD'),
            'cancellation_policies' => Arr::get($pricingData, 'cancellation_policies', []),
            'meal_plans_available' => Arr::get($pricingData, 'meal_plans_available', []),
            'meal_plan' => Arr::get($pricingData, 'meal_plan', ''),
            'room_image' => $roomImage,
            'room_id' => $roomId,
        ];
    }

    public function filterMatchingRooms(array $dataSecondSearch, array $dataFirstSearch): array
    {
        $matchedRooms = [];
        $allRoomsSecondSearch = [];
        foreach ($dataSecondSearch['result'] as $groups) {
            foreach (Arr::get($groups, 'room_groups', []) as $roomGroup) {
                foreach (Arr::get($roomGroup, 'rooms', []) as $room) {
                    $allRoomsSecondSearch[] = $room;
                }
            }
        }

        // Get room_combinations from $dataSecondSearch
        $roomCombinations = $dataSecondSearch['result'][0]['room_combinations'] ?? [];

        // 1. First, find unique rooms by filter
        foreach ($dataFirstSearch as $search) {
            foreach ($allRoomsSecondSearch as $room) {
                $roomCodeMatch = (
                    (isset($room['room_type']) && $room['room_type'] == $search['room_code']));
                $rateCodeMatch = (
                    (isset($room['rate_plan_code']) && $room['rate_plan_code'] == $search['rate_code']));
                $roomNumMatch = (
                    (isset($room['supplier_room_id']) && $room['supplier_room_id'] == $search['supplier_room_id']));

                if ($roomCodeMatch && $rateCodeMatch && $roomNumMatch) {
                    $uniqueKey = $room['room_type'].'|'.$room['rate_plan_code'].'|'.($room['supplier_room_id'] ?? '').'|'.($room['booking_item'] ?? '');
                    if (! isset($uniqueKeys[$uniqueKey])) {
                        $matchedRooms[] = $room;
                        $uniqueKeys[$uniqueKey] = true;
                    }
                }
            }
        }

        logger('CheckQuote Matched Rooms: ', ['matchedRooms' => $matchedRooms, 'dataFirstSearch' => $dataFirstSearch, 'allRoomsSecondSearch' => $allRoomsSecondSearch, 'roomCombinations' => $roomCombinations]);

        // 2. Collect booking_item of all found rooms
        $matchedBookingItems = array_column($matchedRooms, 'booking_item');
        $matchedBookingItems = array_filter($matchedBookingItems); // remove null

        // 3. For each combination, check if it is fully covered by found rooms
        $parentMap = [];
        foreach ($roomCombinations as $parent => $children) {
            // If all booking_item from combination are in matchedRooms
            if (count($children) > 0 && ! array_diff($children, $matchedBookingItems)) {
                foreach ($children as $child) {
                    $parentMap[$child] = $parent;
                }
            }
        }

        // 4. Set parent_booking_item for each room
        foreach ($matchedRooms as &$room) {
            $room['parent_booking_item'] = isset($room['booking_item']) && isset($parentMap[$room['booking_item']])
                ? $parentMap[$room['booking_item']]
                : null;
        }
        unset($room);

        return $matchedRooms;
    }

    /**
     * Compare sums for specified fields between two room arrays.
     */
    public function compareFieldSums(array $fieldsToCompare, array $dataFirstSearch, array $matchedRooms): array
    {
        $sumsFirstSearch = [];
        $sumsCurrentSearch = [];
        foreach ($fieldsToCompare as $field) {
            $sumsFirstSearch[$field] = array_sum(array_column($dataFirstSearch, $field));
            $sumsCurrentSearch[$field] = array_sum(array_column($matchedRooms, $field));
        }
        $differences = [];
        foreach ($fieldsToCompare as $field) {
            $a = (float) $sumsFirstSearch[$field];
            $b = (float) $sumsCurrentSearch[$field];
            $differences[$field] = abs($a - $b) > 0.00001;
        }
        $conclusion = in_array(true, $differences, true) ? 'difference' : 'match';

        return [
            'current_search_sums' => $sumsCurrentSearch,
            'first_search_sums' => $sumsFirstSearch,
            'differences' => $differences,
            'conclusion' => $conclusion,
        ];
    }

    public function moveBookingItem($request, $supplier, $booking_item)
    {
        if (is_null($booking_item)) {
            return $this->sendError('booking_item is null');
        }
        if (($supplier === SupplierNameEnum::HBSI->value || $supplier === SupplierNameEnum::HOTEL_TRADER->value)
            && Cache::get('room_combinations:'.$booking_item)) {
            $hotelService = new HotelCombinationService($supplier);
            $hotelService->updateBookingItemsData($booking_item);
        }

        $attempts = 0;
        while ($attempts < 5 && ! ApiBookingItemRepository::isComleteCache($booking_item)) {
            sleep(1);
            $attempts++;
        }
        if (! ApiBookingItemRepository::isComleteCache($booking_item)) {
            return $this->sendError('booking_item - this item is single');
        }

        // Get booking_id (first search) by booking_item (current search)
        $booking_id = ApiBookingInspectorRepository::getBookIdByBookingItem($request->booking_item);
        $filters = ['booking_item' => $booking_item, 'booking_id' => $booking_id];

        $this->supplierRegistry->get(SupplierNameEnum::from($supplier))->addItem($filters, $supplier, 'check_quote');

        $apiBookingItemFirstSearch = ApiBookingItem::where('booking_item', $request->booking_item)->first();
        $apiBookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();
        if ($apiBookingItem) {
            $apiBookingItem->email_verified = $apiBookingItemFirstSearch->email_verified;
            $apiBookingItem->save();
        }
        if ($apiBookingItemFirstSearch) {
            $apiBookingItemFirstSearch->checked_booking_item = $booking_item;
            $apiBookingItemFirstSearch->save();
        }
    }
}
