<?php

namespace Modules\API\Suppliers\Transformers\Expedia;

use App\Models\ExpediaContent;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\Resolvers\Deposits\DepositResolver;
use Modules\API\PricingAPI\Resolvers\DescriptiveContent\DescriptiveContentResolver;
use Modules\API\PricingAPI\ResponseModels\HotelResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponseFactory;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
use Modules\API\Suppliers\Enums\CancellationPolicyTypesEnum;
use Modules\API\Suppliers\Enums\Expedia\PolicyCode;
use Modules\API\Suppliers\Transformers\BaseHotelPricingTransformer;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class ExpediaHotelPricingTransformer extends BaseHotelPricingTransformer
{
    private ExpediaPricingRulesApplier $pricingRulesApplier;

    private const TA_CLIENT = 'https://developer.expediapartnersolutions.com/terms/en';

    private const TA_AGENT = 'https://developer.expediapartnersolutions.com/terms/agent/en/';

    public array $bookingItems = [];

    public function __construct(
        private array $roomCombinations = [],
        private array $ratings = [],
        private string $currency = 'USD',
        private string $query_package = '',
        private string $supplier_id = '',
    ) {}

    public function ExpediaToHotelResponse(array $supplierResponse, array $query, string $search_id, array $pricingRules, array $pricingExclusionRules, array $giataIds): \Generator
    {
        $this->initializePricingData($query, $pricingExclusionRules, $giataIds, $search_id);
        $this->fetchSupplierRepositoryData($search_id, $giataIds);

        $this->query_package = $query['query_package'];
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()?->id;

        $this->pricingRulesApplier = new ExpediaPricingRulesApplier($query, $pricingRules);

        $propertyIds = array_map(fn ($item) => $item['property_id'], $supplierResponse);
        $this->ratings = ExpediaContent::whereIn('property_id', $propertyIds)->pluck('rating', 'property_id')->toArray();

        foreach ($supplierResponse as $propertyGroup) {
            yield $this->setHotelResponse($propertyGroup, $query);
        }
    }

    private function getRating($defaultRating, $giata)
    {
        return (isset($this->priorityContentFromSupplierRepo[$giata]) && isset($this->priorityContentFromSupplierRepo[$giata]['rating']))
            ? $this->priorityContentFromSupplierRepo[$giata]['rating']
            : ($defaultRating);
    }

    public function setHotelResponse(array $propertyGroup, array $query): array
    {
        $giataId = Arr::get($propertyGroup, 'giata_id');
        $this->roomCombinations = [];
        $destination = $this->giata[$giataId]['city'] ?? $this->destinationData;

        $hotelResponse = HotelResponseFactory::create();
        $hotelResponse->setGiataHotelId($giataId);
        $hotelResponse->setDistanceFromSearchLocation($this->giata[$giataId]['distance'] ?? 0);
        $hotelResponse->setRating($this->getRating($this->ratings[Arr::get($propertyGroup, 'property_id')] ?? '', $giataId));
        $hotelResponse->setHotelName(Arr::get($propertyGroup, 'hotel_name', ''));
        $hotelResponse->setBoardBasis(Arr::get($propertyGroup, 'board_basis', ''));
        $hotelResponse->setSupplier(SupplierNameEnum::EXPEDIA->value);
        $hotelResponse->setSupplierHotelId(Arr::get($propertyGroup, 'property_id'));
        $hotelResponse->setDestination($destination);
        $hotelResponse->setMealPlansAvailable(Arr::get($propertyGroup, 'meal_plans_available', ''));
        $hotelResponse->setPayAtHotelAvailable(Arr::get($propertyGroup, 'pay_at_hotel_available', ''));
        $hotelResponse->setPayNowAvailable(Arr::get($propertyGroup, 'pay_now_available', ''));
        $countRefundableRates = $this->fetchCountRefundableRates($propertyGroup);
        $hotelResponse->setNonRefundableRates(Arr::get($countRefundableRates, 'non_refundable_rates', ''));
        $hotelResponse->setRefundableRates(Arr::get($countRefundableRates, 'refundable_rates', ''));
        $hotelResponse->setHoldable($this->features[$propertyGroup['giata_id']]['holdable'] ?? false);
        $roomGroups = [];
        $lowestPrice = 100000;
        foreach ($propertyGroup['rooms'] as $roomGroup) {
            $roomGroupsData = $this->setRoomGroupsResponse($roomGroup, $propertyGroup, $query);
            if (! $roomGroupsData) {
                continue;
            }
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

        $hotelResponse->setSupplierInformation([
            'supplier_terms_and_conditions_client' => self::TA_CLIENT,
            'supplier_terms_and_conditions_agent' => self::TA_AGENT,
        ]);

        $descriptiveContent = DescriptiveContentResolver::getHotelLevel(Arr::get($this->descriptiveContent, $giataId, []), $this->query, $giataId);
        $hotelResponse->setDescriptiveContent($descriptiveContent);

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

    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup, array $query): ?array
    {
//        dd($roomGroup, $propertyGroup, $query);

        $giataId = Arr::get($propertyGroup, 'giata_id');

        $basicHotelData = Arr::get($this->basicHotelData, $propertyGroup['giata_id']);
        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');

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
        $roomsResponse = [];
        $priceRoomData = [];
        foreach ($roomGroup['rates'] as $key => $room) {
            foreach ($room['bed_groups'] as $bedGroupKey => $bedGroup) {
                $rateId = Arr::get($room, 'id', '');
                // exclude rate codes from the response according to excludeRules
                if (in_array($rateId, $this->exclusionRates)) {
                    continue;
                }
                $roomData = $this->setRoomResponse((array) $room, $roomGroup, $propertyGroup, $giataId, $bedGroup, $query);
                if (empty($roomData)) {
                    continue;
                }
                $roomResponse = $roomData['roomResponse'];
                $pricingRulesApplierRoom = $roomData['pricingRulesApplier'];
                $rooms[$key][$bedGroupKey] = $roomResponse;
                $roomsResponse[] = $roomResponse;
                $priceRoomData[$key][$bedGroupKey] = $pricingRulesApplierRoom;
            }
        }
        if (empty($priceRoomData)) {
            return null;
        }

        $roomGroupsResponse->setRooms($roomsResponse);

        $lowestPricedRoom = 1000000;
        $keyLowestPricedRoom = array_key_first($priceRoomData); // set first as default
        $keyLowestPricedBedGroup = array_key_first($priceRoomData[$keyLowestPricedRoom]); // set first as default

        foreach ($priceRoomData as $key => $priceRoom) {
            foreach ($priceRoom as $bedGroupKey => $bedGroupPrice) {
                if ($bedGroupPrice['total_price'] >= 0 && $bedGroupPrice['total_price'] < $lowestPricedRoom) {
                    $lowestPricedRoom = $bedGroupPrice['total_price'];
                    $keyLowestPricedRoom = $key;
                    $keyLowestPricedBedGroup = $bedGroupKey;
                }
            }
        }

        /** return lowest priced room data */
        $totalPrice = $priceRoomData[$keyLowestPricedRoom][$keyLowestPricedBedGroup]['total_price'] ?? 0.0;
        $roomGroupsResponse->setTotalPrice($totalPrice);
        $roomGroupsResponse->setTotalTax($priceRoomData[$keyLowestPricedRoom][$keyLowestPricedBedGroup]['total_tax'] ?? 0.0);
        $roomGroupsResponse->setTotalFees($priceRoomData[$keyLowestPricedRoom][$keyLowestPricedBedGroup]['total_fees'] ?? 0.0);
        $roomGroupsResponse->setTotalNet($priceRoomData[$keyLowestPricedRoom][$keyLowestPricedBedGroup]['total_net'] ?? 0.0);
        $roomGroupsResponse->setNonRefundable($rooms[$keyLowestPricedRoom][$keyLowestPricedBedGroup]['non_refundable']);
        $roomGroupsResponse->setRateId(intval($rooms[$keyLowestPricedRoom][$keyLowestPricedBedGroup]['rate_id']) ?? null);
        $roomGroupsResponse->setCancellationPolicies($rooms[$keyLowestPricedRoom][$keyLowestPricedBedGroup]['cancellation_policies']);

        /** @var RoomResponse $roomResponse */
        $roomResponse = app(RoomResponse::class);
        $roomResponseLowestPrice = $roomResponse->fromArray($rooms[$keyLowestPricedRoom]);

        $rating = Arr::get($this->giata, "$giataId.rating", 0);
        $roomGroupsResponse->setDeposits(DepositResolver::getRateLevel($roomResponseLowestPrice, Arr::get($this->depositInformation, $giataId, []), $query, $giataId, $rating, $totalPrice));

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    public function setRoomResponse(array $rate, array $roomGroup, array $propertyGroup, int $giataId, array $bedGroup, array $query): array
    {
        $roomName = $roomGroup['room_name'] ?? '';
        // exclude and names from the response according to excludeRules
        if (in_array($roomName, $this->exclusionRoomNames)) {
            return [];
        }

        $basicHotelData = Arr::get($this->basicHotelData, $giataId);
        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');

        $supplierRoomId = intval($roomGroup['id']) ?? null;
//        $expediaUnifiedRoomCodes = Arr::get($this->unifiedRoomCodes, ContentSourceEnum::EXPEDIA->value, []);
//        $unifiedRoomCode = Arr::get($expediaUnifiedRoomCodes, "$giataId.$supplierRoomId", Arr::get($roomGroup, 'id', '')) ?? '';
//        $srRoomId = Arr::get($this->roomIdByUnifiedCode, "$giataId.$unifiedRoomCode", '');

        $unifiedRoomCode = Arr::get($roomGroup, 'unified_room_code', '');
        $srRoomId = Arr::get($this->roomIdByUnifiedCode, "$giataId.$unifiedRoomCode", '');

        $rateId = Arr::get($rate, 'id', '');
        /**  enrichment Pricing Rules / Application of Pricing Rules */
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['commission_amount'] = 0.0;
        $occupancy_pricing = $rate['occupancy_pricing'];

        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply(
                giataId: $giataId,
                roomsPricingArray: $occupancy_pricing,
                roomName: $roomGroup['room_name'] ?? '',
                roomCode: intval($roomGroup['id']) ?? null,
                roomType: $roomGroup['room_type'] ?? '',
                rateCode: $rateId,
                srRoomId: $srRoomId,
            );
        } catch (Exception $e) {
            Log::error('ExpediaHotelPricingTransformer | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());
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

        if ($_promotions = Arr::get($rate, 'promotions')) {
            $promotions = $this->getPromotions($_promotions);
        }

        $roomResponse = RoomResponseFactory::create();
        $roomResponse->setUnifiedRoomCode($unifiedRoomCode);
        $roomResponse->setGiataRoomCode($rate['giata_room_code'] ?? '');
        $roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
        $roomResponse->setQueryPackage($this->query_package);
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($roomName);
        $roomResponse->setSupplierRoomCode($supplierRoomId);
        $roomResponse->setSupplierBedGroups(Arr::get($bedGroup, 'id'));
        $roomResponse->setRoomType('');
        $roomResponse->setRateDescription($rate['description'] ?? '');
        $roomResponse->setRateId($rateId);
        $roomResponse->setRatePlanCode($rateId);
        $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
        $roomResponse->setTotalTax($pricingRulesApplier['total_tax']);
        $roomResponse->setTotalFees($pricingRulesApplier['total_fees']);
        $roomResponse->setTotalNet($pricingRulesApplier['total_net']);
        /** Commission tracking data */
        $roomResponse->setCommissionAmount($pricingRulesApplier['commission_amount']);

        if ($isCommissionTracking && ! ($pricingRulesApplier['commission_amount'] > 0)) {
            $roomResponse->setCommissionAmount(0.0);
        }
        $roomResponse->setCommissionableAmount(
            max(0.0, $roomResponse->getTotalPrice() - $roomResponse->getTotalTax())
        );

//        $resolvedPolicies = CancellationPolicyResolver::getRateLevel($roomResponse, Arr::get($this->cancellationPolicies, $giataId, []), $query, $giataId);
//        if (! is_array($cancellationPolicies) || Arr::isAssoc($cancellationPolicies)) {
//            $cancellationPolicies = array_filter([$cancellationPolicies]);
//        }
//        if (! is_array($resolvedPolicies) || Arr::isAssoc($resolvedPolicies)) {
//            $resolvedPolicies = array_filter([$resolvedPolicies]);
//        }
//        $cancellationPolicies = array_merge($cancellationPolicies, $resolvedPolicies);
//        $roomResponse->setCancellationPolicies(array_values($cancellationPolicies));

        $roomResponse->setPackageDeal(Arr::get($rate, 'sale_scenario.package', false));
        $roomResponse->setDistribution(Arr::get($rate, 'sale_scenario.distribution', false));
        $roomResponse->setPromotions($promotions);
        $roomResponse->setNonRefundable(! $rate['refundable']);

        // Force penatly date to now if the rate is non-refundable
        if ($roomResponse->getNonRefundable()) {
            $penaltyDate = Carbon::now();
        }

        $roomResponse->setPenaltyDate($penaltyDate);

        /** Commission tracking data */
        $roomResponse->setCommissionableAmount($roomResponse->getTotalPrice() - $roomResponse->getTotalTax());
        $roomResponse->setCommissionAmount(Arr::get($pricingRulesApplier, 'commission_amount', 0));

        $roomResponse->setCurrency($this->currency);

        $roomResponse->setBedConfigurations(Arr::get($bedGroup, 'configuration', []));

        $roomResponse->setBreakdown($this->getBreakdown($occupancy_pricing));

        $bookingItem = Str::uuid()->toString();
        $roomResponse->setBookingItem($bookingItem);

        $roomResponse->setPricingRulesAppliers($this->transformPricingRulesAppliers($pricingRulesApplier));

        $rating = Arr::get($this->ratings, "$giataId.rating", 0);
        $roomResponse->setDeposits(DepositResolver::getRateLevel($roomResponse, Arr::get($this->depositInformation, $giataId, []), $this->query, $giataId, $rating));

        $this->roomCombinations[$bookingItem] = [$bookingItem];

        $this->bookingItems[] = [
            'booking_item' => $bookingItem,
            'supplier_id' => $this->supplier_id,
            'search_id' => $this->search_id,
            'rate_type' => ItemTypeEnum::COMPLETE->value,
            'booking_item_data' => json_encode([
                'hotel_id' => $propertyGroup['giata_id'],
                'room_id' => $roomGroup['id'],
                'rate' => $rateId,
                'supplier' => SupplierNameEnum::EXPEDIA->value,
                'bed_groups' => Arr::get($bedGroup, 'id'),
                'hotel_supplier_id' => $propertyGroup['property_id'],
                'query_package' => $this->query_package,
            ]),
            'booking_pricing_data' => json_encode($roomResponse->toArray()),
            'created_at' => Carbon::now()->toDateTimeString(),
            'cache_checkpoint' => Arr::get($propertyGroup, 'giata_id', 0).':'.Arr::get($roomGroup, 'id', 0).':'.$rateId.':'.SupplierNameEnum::EXPEDIA->value,
        ];

        $roomResponse->setDescriptiveContent(DescriptiveContentResolver::getRateLevel($roomResponse, Arr::get($this->descriptiveContent, $giataId, []), $this->query, $giataId));

        return ['roomResponse' => $roomResponse->toArray(), 'pricingRulesApplier' => $pricingRulesApplier];
    }

    private function getAmenitiesFromRate(array $rate): array
    {
        $amenities = Arr::get($rate, 'amenities', []);

        return array_values(array_map(fn (array $amenity) => ['name' => $amenity['name']], $amenities));
    }

    private function getFeesValue(array $roomsPricingArray, int $nights): float
    {

        return collect($roomsPricingArray)->reduce(function ($carry, $arrFee) {
            return $carry + floatval($arrFee['request_currency']['value']);
        }, 0) / $nights;
    }
    private function getBreakdown(array $roomsPricingArray): array
    {
        $breakdown = [];
        $breakdownStay = [];
        $breakdownFees = [];

        foreach ($this->occupancy as $room) {

            $roomsKey = isset($room['children_ages']) ? $room['adults'].'-'.implode(',', $room['children_ages']) : $room['adults'];

            $nights = count($roomsPricingArray[$roomsKey]['nightly']);
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
                    if (! isset($breakdownStay[$stay][$key]['amount'])) {
                        $breakdownStay[$stay][$key]['amount'] = 0;
                    }
                    $breakdownStay[$stay][$key]['amount'] += $expenseItem['value'];
                }
            }

            if (isset($roomsPricingArray[$roomsKey]['fees'])) {
                foreach ($roomsPricingArray[$roomsKey]['fees'] as $fee => $expenseItem) {
                    $breakdownFees[$fee]['type'] = 'fee';
                    $breakdownFees[$fee]['title'] = $fee;
                    $breakdownFees[$fee]['collected_by'] = 'direct';

                    if (! isset($breakdownFees[$fee]['amount'])) {
                        $breakdownFees[$fee]['amount'] = 0;
                    }

                    if (! isset($breakdownFees[$fee]['local_amount'])) {
                        $breakdownFees[$fee]['local_amount'] = 0;
                    }

                    $breakdownFees[$fee]['amount'] += floatval($expenseItem['request_currency']['value']);
                    $breakdownFees[$fee]['local_amount'] += floatval($expenseItem['request_currency']['value']);
                    $breakdownFees[$fee]['local_currency'] = $expenseItem['request_currency']['currency'];
                    /* Todo: move fees to pay at property */
                    //$breakdownFees[$fee]['local_amount'] += floatval($expenseItem['billable_currency']['value']);
                    //$breakdownFees[$fee]['local_currency'] = $expenseItem['billable_currency']['currency'];
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

        foreach ($_promotions as $type => $promotion) {
            if (! Arr::has($promotion, 'description') && is_array($promotion)) {
                $promotions = array_merge($promotions, $this->getPromotions($promotion));
            } else {
                $promotions[] = [
                    'type' => $type,
                    'description' => Arr::get($promotion, 'description', '-'),
                ];
            }
        }

        return $promotions;
    }
}
