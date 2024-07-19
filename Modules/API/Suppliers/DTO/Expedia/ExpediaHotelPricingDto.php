<?php

namespace Modules\API\Suppliers\DTO\Expedia;

use App\Models\ExpediaContent;
use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\GiataProperty;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomResponseFactory;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
use Modules\API\Suppliers\Enums\CancellationPolicyTypesEnum;
use Modules\API\Suppliers\Enums\Expedia\PolicyCode;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class ExpediaHotelPricingDto
{
    private ExpediaPricingRulesApplier $pricingRulesApplier;

    private string $search_id;

    private string $currency;

    private float $current_time;

    private ?string $destinationData;

    private array $bookingItems;

    private array $roomCombinations;

    private array $occupancy;

    private array $ratings;

    private array $giata;

    public function __construct()
    {
        $this->current_time = microtime(true);
    }

    public function ExpediaToHotelResponse(array $supplierResponse, array $query, string $search_id, array $pricingRules): array
    {
        $this->search_id = $search_id;
        $this->bookingItems = [];
        $this->occupancy = $query['occupancy'];

        $this->pricingRulesApplier = new ExpediaPricingRulesApplier($query, $pricingRules);

        $giataIds = array_map(function ($item) {
            return $item['giata_id'];
        }, $supplierResponse);

        $propertyIds = array_map(function ($item) {
            return $item['property_id'];
        }, $supplierResponse);

        $this->ratings = ExpediaContent::whereIn('property_id', $propertyIds)
            ->select(['property_id', 'rating'])
            ->get()
            ->keyBy('property_id')
            ->toArray();

        $latitude = Arr::get($query, 'latitude', 0);
        $longitude = Arr::get($query, 'longitude', 0);

        $this->giata = GiataProperty::whereIn('code', $giataIds)
            ->selectRaw('code, rating, name, city, 6371 * 2 * ASIN(SQRT(POWER(SIN((latitude - abs(?)) * pi()/180 / 2), 2) + COS(latitude * pi()/180 ) * COS(abs(?) * pi()/180) * POWER(SIN((longitude - ?) *  pi()/180 / 2), 2))) as distance', [$latitude, $latitude, $longitude])
            ->get()
            ->keyBy('code')
            ->map(function ($item) {
                return [
                    'city' => $item->city,
                    'distance' => $item->distance,
                ];
            })
            ->toArray();

        if (isset($query['destination'])) {
            $this->destinationData = GiataGeography::where('city_id', $query['destination'])
                ->select([
                    DB::raw("CONCAT(city_name, ', ', locale_name, ', ', country_name) as full_location"),
                ])
                ->first()->full_location ?? '';
        } elseif (isset($query['place'])) {
            $this->destinationData = GiataPlace::where('key', $query['place'])
                ->select([
                    DB::raw("CONCAT(name_primary, ', ', type, ', ', country_code) as full_location"),
                ])
                ->first()->full_location ?? '';
        } else {
            $this->destinationData = null;
        }

        $hotelResponse = [];
        foreach ($supplierResponse as $propertyGroup) {
            $hotelResponse[] = $this->setHotelResponse($propertyGroup);
        }

        return ['response' => $hotelResponse, 'bookingItems' => $this->bookingItems];
    }

    public function setHotelResponse(array $propertyGroup): array
    {
        $this->roomCombinations = [];
        $destination = $this->giata[$propertyGroup['giata_id']]['city'] ?? $this->destinationData;
        $hotelResponse = HotelResponseFactory::create();
        $hotelResponse->setGiataHotelId($propertyGroup['giata_id']);
        $hotelResponse->setDistanceFromSearchLocation($this->giata[$propertyGroup['giata_id']]['distance'] ?? 0);
        $hotelResponse->setRating($this->ratings[$propertyGroup['property_id']]['rating'] ?? '');
        $hotelResponse->setHotelName($propertyGroup['hotel_name'] ?? '');
        $hotelResponse->setBoardBasis(($propertyGroup['board_basis'] ?? ''));
        $hotelResponse->setSupplier(SupplierNameEnum::EXPEDIA->value);
        $hotelResponse->setSupplierHotelId($propertyGroup['property_id']);
        $hotelResponse->setDestination($destination);
        $hotelResponse->setMealPlansAvailable($propertyGroup['meal_plans_available'] ?? '');
        $hotelResponse->setPayAtHotelAvailable($propertyGroup['pay_at_hotel_available'] ?? '');
        $hotelResponse->setPayNowAvailable($propertyGroup['pay_now_available'] ?? '');
        $countRefundableRates = $this->fetchCountRefundableRates($propertyGroup);
        $hotelResponse->setNonRefundableRates($countRefundableRates['non_refundable_rates'] ?? '');
        $hotelResponse->setRefundableRates($countRefundableRates['refundable_rates'] ?? '');
        $roomGroups = [];
        $lowestPrice = 100000;
        foreach ($propertyGroup['rooms'] as $roomGroup) {
            $roomGroupsData = $this->setRoomGroupsResponse($roomGroup, $propertyGroup);
            $roomGroups[] = $roomGroupsData['roomGroupsResponse'];
            $lowestPricedRoom = $roomGroupsData['lowestPricedRoom'];
            if ($lowestPricedRoom > 0 && $lowestPricedRoom < $lowestPrice) {
                $lowestPrice = $lowestPricedRoom;
            }
        }
        $hotelResponse->setRoomGroups($roomGroups);

        $hotelResponse->setRoomCombinations($this->roomCombinations);
        $this->roomCombinations = [];

        $hotelResponse->setLowestPricedRoomGroup($lowestPrice != 100000 ? $lowestPrice : '');

        return $hotelResponse->toArray();
    }

    private function fetchCountRefundableRates(array $propertyGroup): array
    {
        $refundableRates = [];
        $nonRefundableRates = [];
        foreach ($propertyGroup['rooms'] as $roomGroup) {
            foreach ($roomGroup['rates'] as $rate) {
                if ($rate['refundable']) {
                    $refundableRates[] = $rate['id'];
                } else {
                    $nonRefundableRates[] = $rate['id'];
                }
            }
        }

        return ['refundable_rates' => implode(',', $refundableRates), 'non_refundable_rates' => implode(',', $nonRefundableRates)];
    }

    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup): array
    {
        $giataId = $propertyGroup['giata_id'];

        $roomGroupsResponse = RoomGroupsResponseFactory::create();

        $roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
        $roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');
        $roomGroupsResponse->setMealPlan($roomGroup['meal_plan'] ?? '');
        $roomGroupsResponse->setRateDescription($roomGroup['rate_description'] ?? '');
        $roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');

        $firstRoomCapacityKey = array_key_first($roomGroup['rates'][0]['occupancy_pricing']);
        $this->currency = $roomGroup['rates'][0]['occupancy_pricing'][$firstRoomCapacityKey]['nightly'][0][0]['currency'];
        $roomGroupsResponse->setCurrency($this->currency ?? 'USD');

        $rooms = [];
        $priceRoomData = [];
        foreach ($roomGroup['rates'] as $key => $room) {

            foreach ($room['bed_groups'] as $bedGroupKey => $bedGroup)
            {
                $roomData = $this->setRoomResponse((array) $room, $roomGroup, $propertyGroup, $giataId, $bedGroup);
                $roomResponse = $roomData['roomResponse'];
                $pricingRulesApplierRoom = $roomData['pricingRulesApplier'];
                $rooms[$bedGroupKey] = $roomResponse;
                $priceRoomData[$bedGroupKey] = $pricingRulesApplierRoom;
            }
        }
        $roomGroupsResponse->setRooms($rooms);

        $lowestPricedRoom = 100000;
        $keyLowestPricedRoom = 0;
        foreach ($priceRoomData as $key => $priceRoom) {
            if ($priceRoom['total_price'] > 0 && $priceRoom['total_price'] < $lowestPricedRoom) {
                $lowestPricedRoom = $priceRoom['total_price'];
                $keyLowestPricedRoom = $key;
            }
        }

        $cancellationPolicies = $rooms[$keyLowestPricedRoom]['cancellation_policies'];

        /** return lowest priced room data */
        $roomGroupsResponse->setTotalPrice($priceRoomData[$keyLowestPricedRoom]['total_price'] ?? 0.0);
        $roomGroupsResponse->setTotalTax($priceRoomData[$keyLowestPricedRoom]['total_tax'] ?? 0.0);
        $roomGroupsResponse->setTotalFees($priceRoomData[$keyLowestPricedRoom]['total_fees'] ?? 0.0);
        $roomGroupsResponse->setTotalNet($priceRoomData[$keyLowestPricedRoom]['total_net'] ?? 0.0);
        $roomGroupsResponse->setMarkup($priceRoomData[$keyLowestPricedRoom]['markup'] ?? 0.0);


        $roomGroupsResponse->setNonRefundable($rooms[$keyLowestPricedRoom]['non_refundable']);
        $roomGroupsResponse->setRateId(intval($rooms[$keyLowestPricedRoom]['rate_id']) ?? null);
        $roomGroupsResponse->setCancellationPolicies($rooms[$keyLowestPricedRoom]['cancellation_policies']);

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    public function setRoomResponse(array $rate, array $roomGroup, array $propertyGroup, int $giataId, array $bedGroup): array
    {
        /**  enrichment Pricing Rules / Application of Pricing Rules */
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['markup'] = 0.0;
        $occupancy_pricing = $rate['occupancy_pricing'];
        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply($giataId, $occupancy_pricing);
        } catch (Exception $e) {
            Log::error('ExpediaHotelPricingDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());
        }

        if ($pricingRulesApplier['total_price'] == 0.0) {
            Log::error('ExpediaHotelPricingDto | setRoomGroupsResponse ', ['error' => 'total_price == 0.0']);
        }

        /** https://developers.expediagroup.com/docs/products/rapid/resources/reference/constructing-cancellation-policies */
        $cancelPenalty = $rate['cancel_penalties'];
        $cancellationPolicies = [];
        $penaltyDate = null;

        foreach ($cancelPenalty as $key => $penalty) {
            $data = [];

            $data['description'] = PolicyCode::General->value;
            $data['type'] = PolicyCode::getObeCode()->value;

            if (isset($penalty['start'])) {
                $data['penalty_start_date'] = $penalty['start'];

                if ($penaltyDate === null || $penaltyDate > $data['penalty_start_date']) {
                    $penaltyDate = $data['penalty_start_date'];
                }
            }
            if (isset($penalty['end'])) {
                $data['penalty_end_date'] = $penalty['end'];
            }
            if (isset($penalty['percent'])) {
                $data['percentage'] = $penalty['percent'];
            }
            if (isset($penalty['amount'])) {
                $data['amount'] = $penalty['amount'];
            }
            if (isset($penalty['nights'])) {
                $data['nights'] = $penalty['nights'];
            }
            if (isset($penalty['currency'])) {
                $data['currency'] = $penalty['currency'];
            }

            $cancellationPolicies[] = $data;
        }

        if ($penaltyDate === null) {
            $penaltyDate = date('Y-m-d');

            $cancellationPolicies[] = [
                'description' => PolicyCode::General->value,
                'type' => CancellationPolicyTypesEnum::General->value,
                'penalty_start_date' => $penaltyDate,
                'percentage' => '100',
            ];
        }

        $promotions = [];

        if ($_promotions = Arr::get($rate, 'promotions'))
        {
            $promotions = $this->getPromotions($_promotions);
        }

        $roomResponse = RoomResponseFactory::create();
        $roomResponse->setGiataRoomCode($rate['giata_room_code'] ?? '');
        $roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
        $roomResponse->setPenaltyDate($penaltyDate);
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($roomGroup['room_name'] ?? '');
        $roomResponse->setSupplierRoomCode(intval($roomGroup['id']) ?? null);
        $roomResponse->setSupplierBedGroups(Arr::get($bedGroup, 'id'));
        $roomResponse->setRoomType('');
        $roomResponse->setRateDescription($rate['description'] ?? '');
        $roomResponse->setRateId($rate['id'] ?? '');
        $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
        $roomResponse->setTotalTax($pricingRulesApplier['total_tax']);
        $roomResponse->setTotalFees($pricingRulesApplier['total_fees']);
        $roomResponse->setTotalNet($pricingRulesApplier['total_net']);
        $roomResponse->setMarkup($pricingRulesApplier['markup']);
        $roomResponse->setCancellationPolicies($cancellationPolicies);
        $roomResponse->setPackageDeal(Arr::get($rate, 'sale_scenario.package', false));
        $roomResponse->setPromotions($promotions);
        $roomResponse->setNonRefundable($rate['refundable']);

        $roomResponse->setCurrency($this->currency);

        $roomResponse->setBedConfigurations(Arr::get($bedGroup, 'configuration'));

        $roomResponse->setBreakdown($this->getBreakdown($occupancy_pricing));

        $bookingItem = Str::uuid()->toString();
        $roomResponse->setBookingItem($bookingItem);

        $this->roomCombinations[$bookingItem] = [$bookingItem];

        $this->bookingItems[] = [
            'booking_item' => $bookingItem,
            'supplier_id' => Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id,
            'search_id' => $this->search_id,
            'rate_type' => ItemTypeEnum::COMPLETE->value,
            'booking_item_data' => json_encode([
                'hotel_id' => $propertyGroup['giata_id'],
                'room_id' => $roomGroup['id'],
                'rate' => $rate['id'],
                'bed_groups' => Arr::get($bedGroup, 'id'),
                'hotel_supplier_id' => $propertyGroup['property_id'],
            ]),
            'booking_pricing_data' => json_encode($roomResponse->toArray()),
            'created_at' => Carbon::now(),
        ];

        return ['roomResponse' => $roomResponse->toArray(), 'pricingRulesApplier' => $pricingRulesApplier];
    }

    private function getBreakdown(array $roomsPricingArray): array
    {
        $breakdown = [];
        $breakdownStay = [];
        $breakdownFees = [];

        foreach ($this->occupancy as $room) {

            $roomsKey = isset($room['children_ages']) ? $room['adults'].'-'.implode(',', $room['children_ages']) : $room['adults'];

            foreach ($roomsPricingArray[$roomsKey]['nightly'] as $night => $expenseItems) {
                foreach ($expenseItems as $expenseItem) {
                    $key = '';
                    if ($expenseItem['type'] === 'base_rate') {
                        $key = 'base_fare';
                        $breakdown[$night][$key]['type'] = 'base_fare';
                        $breakdown[$night][$key]['title'] = 'Room Base Fare';
                    } elseif (str_contains($expenseItem['type'], 'tax')) {
                        $key = 'tax';
                        $breakdown[$night][$key]['type'] = 'tax inclusive';
                        $breakdown[$night][$key]['title'] = $expenseItem['type'];
                    } elseif (str_contains($expenseItem['type'], 'fee')) {
                        $key = 'fee';
                        $breakdown[$night][$key]['type'] = 'fee inclusive';
                        $breakdown[$night][$key]['title'] = $expenseItem['type'];
                    }
                    if (! isset($breakdown[$night][$key]['amount'])) {
                        $breakdown[$night][$key]['amount'] = 0;
                    }
                    $breakdown[$night][$key]['amount'] += $expenseItem['value'];
                }
            }

            if (isset($roomsPricingArray[$roomsKey]['stay'])) {
                foreach ($roomsPricingArray[$roomsKey]['stay'] as $stay => $expenseItem) {
                    $key = '';
                    if (str_contains($expenseItem['type'], 'tax')) {
                        $key = 'tax';
                        $breakdownStay[$stay][$key]['type'] = 'tax inclusive';
                        $breakdownStay[$stay][$key]['title'] = $expenseItem['type'];
                    } elseif (str_contains($expenseItem['type'], 'fee')) {
                        $key = 'fee';
                        $breakdownStay[$stay][$key]['type'] = 'fee inclusive';
                        $breakdownStay[$stay][$key]['title'] = $expenseItem['type'];
                    }
                    if (!isset($breakdownStay[$stay][$key]['amount'])) $breakdownStay[$stay][$key]['amount'] = 0;
                    $breakdownStay[$stay][$key]['amount'] += $expenseItem['value'];
                }
            }

            if (isset($roomsPricingArray[$roomsKey]['fees'])) {
                foreach ($roomsPricingArray[$roomsKey]['fees'] as $fee => $expenseItem) {
                    $breakdownFees[$fee]['type'] = 'fee exclusive';
                    $breakdownFees[$fee]['title'] = $fee;

                    if (! isset($breakdownFees[$fee]['amount'])) {
                        $breakdownFees[$fee]['amount'] = 0;
                    }

                    if (! isset($breakdownFees[$fee]['local_amount'])) {
                        $breakdownFees[$fee]['local_amount'] = 0;
                    }

                    $breakdownFees[$fee]['amount'] += floatval($expenseItem['request_currency']['value']);
                    $breakdownFees[$fee]['local_amount'] += floatval($expenseItem['billable_currency']['value']);
                    $breakdownFees[$fee]['local_currency'] = $expenseItem['billable_currency']['currency'];
                }
            }
        }

        $breakdownWithoutKeys = [];
        foreach ($breakdown as $item) {
            $breakdownWithoutKeys[] = array_values($item);
        }

        $breakdownStayWithoutKeys = [];
        foreach ($breakdownStay as $items) {
            foreach ($items as $item) {
                $breakdownStayWithoutKeys[] = json_decode(json_encode($item), false);
            }
        }

        return [
            'nightly' => $breakdownWithoutKeys,
            'stay' => $breakdownStayWithoutKeys,
            'fees' => array_values($breakdownFees),
        ];
    }

    private function getPromotions(mixed $_promotions): array
    {
        $promotions = [];

        foreach ($_promotions as $type => $promotion)
        {
            if (! Arr::has($promotion, 'description') && is_array($promotion))
            {
                $promotions = array_merge($promotions, $this->getPromotions($promotion));
            }
            else
            {
                $promotions[] = [
                    'type' => $type,
                    'description' => Arr::get($promotion, 'description', '-'),
                ];
            }
        }

        return $promotions;
    }
}
