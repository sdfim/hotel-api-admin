<?php

namespace Modules\API\Suppliers\DTO\HBSI;

use App\Models\Supplier;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomResponseFactory;
use Modules\API\PricingRules\HBSI\HbsiPricingRulesApplier;
use Modules\API\Suppliers\Enums\CancellationPolicyTypesEnum;
use Modules\API\Suppliers\Enums\HBSI\PolicyCode;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Tools\PricingDtoTools;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Hotel;

class HbsiHotelPricingDto
{
    private HbsiPricingRulesApplier $pricingRulesApplier;
    private array $mapperSupplierRepository;

    /**
     * @var string[]
     */
    public array $fees = [
        'application fee',
        'banquet service fee',
        'city hotel fee',
        'crib fee',
        'early checkout fee',
        'express handling fee',
        'extra person charge',
        'local fee',
        'maintenance fee',
        'package fee',
        'resort fee',
        'rollaway fee',
        'room service fee',
        'service charge',
    ];

    /**
     * @var string[]
     */
    private array $taxes = [
        'assessment/license tax',
        'bed tax',
        'city tax',
        'country tax',
        'county tax',
        'energy tax',
        'exempt',
        'federal tax',
        'food & beverage tax',
        'goods and services tax (gst)',
        'insurance premium tax',
        'lodging tax',
        'miscellaneous',
        'occupancy tax',
        'room tax',
        'sales tax',
        'standard',
        'state tax',
        'surcharge',
        'surplus lines tax',
        'total tax',
        'tourism tax',
        'vat/gst tax',
        'value added tax (vat)',
    ];

    private const MEAL_PLAN = [
        'AI' => 'All Inclusive',
        'RO' => 'Room Only',
        'BB' => 'Bed & Breakfast',
        'BF' => 'Breakfast',
        'NoM' => 'No Meal',
    ];

    public function __construct(
        private readonly PricingDtoTools $pricingDtoTools,
        private array                    $bookingItems = [],
        private array                    $meal_plans_available = [],
        private array                    $roomCombinations = [],
        private array                    $giata = [],
        private string                   $rate_type = '',
        private string                   $destinationData = '',
        private string                   $search_id = '',
        private string                   $currency = '',
        private int                      $supplier_id = 0,
    ){}

    public function HbsiToHotelResponse(array $supplierResponse, array $query, string $search_id, array $pricingRules, array $giataIds): array
    {
        $supplierRepositoryData = Hotel::has('rooms')->whereIn('giata_code', $giataIds)->get();
        $this->mapperSupplierRepository = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->rooms->mapWithKeys(function ($room) {
                    if (!empty($room->hbsi_data_mapped_name)) {
                        return [
                            $room->hbsi_data_mapped_name => [
                                'description' => $room->description,
                                'name' => $room->name,
                            ],
                        ];
                    }
                    return [];
                })->toArray(),
            ];
        })->toArray();

        $this->search_id = $search_id;
        $this->rate_type = count($query['occupancy']) > 1 ? ItemTypeEnum::SINGLE->value : ItemTypeEnum::COMPLETE->value;
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $this->bookingItems = [];

        $pricingRules = array_column($pricingRules, null, 'property');
        $this->pricingRulesApplier = new HbsiPricingRulesApplier($query, $pricingRules);

        $this->giata = $this->pricingDtoTools->getGiataProperties($query, $giataIds);
        $this->destinationData = $this->pricingDtoTools->getDestinationData($query);

        $hotelResponse = [];
        foreach ($supplierResponse as $key => $propertyGroup) {
            $hotelResponse[] = $this->setHotelResponse($propertyGroup, $key);
        }

        return ['response' => $hotelResponse, 'bookingItems' => $this->bookingItems];
    }

    public function setHotelResponse(array $propertyGroup, int|string $key): array
    {
        $this->roomCombinations = [];
        $hotelResponse = HotelResponseFactory::create();
        $hotelResponse->setGiataHotelId($propertyGroup['giata_id'] ?? 0);
        $hotelResponse->setDistanceFromSearchLocation($this->giata[$propertyGroup['giata_id']]['distance'] ?? 0);
        $hotelResponse->setRating($this->giata[$propertyGroup['giata_id']]['rating'] ?? 0);
        $hotelResponse->setHotelName($this->giata[$propertyGroup['giata_id']]['hotel_name'] ?? '');
        $hotelResponse->setBoardBasis(($propertyGroup['board_basis'] ?? ''));
        $hotelResponse->setSupplier(SupplierNameEnum::HBSI->value);
        $hotelResponse->setSupplierHotelId($key);
        $hotelResponse->setDestination($this->giata[$propertyGroup['giata_id']]['city'] ?? $this->destinationData);

        $hotelResponse->setPayAtHotelAvailable($propertyGroup['pay_at_hotel_available'] ?? '');
        $hotelResponse->setPayNowAvailable($propertyGroup['pay_now_available'] ?? '');

        $roomGroups = [];
        $lowestPrice = 100000;

        foreach ($propertyGroup['rooms'] as $roomGroup) {
            $roomGroupsData = $this->setRoomGroupsResponse($roomGroup, $propertyGroup, $key);
            $roomGroups[] = $roomGroupsData['roomGroupsResponse'];
            $lowestPricedRoom = $roomGroupsData['lowestPricedRoom'];
            if ($lowestPricedRoom > 0 && $lowestPricedRoom < $lowestPrice) {
                $lowestPrice = $lowestPricedRoom;
            }
        }

        $countRefundableRates = $this->fetchCountRefundableRates($roomGroupsData['roomGroupsResponse']);
        $hotelResponse->setNonRefundableRates($countRefundableRates['non_refundable_rates'] ?? '');
        $hotelResponse->setRefundableRates($countRefundableRates['refundable_rates'] ?? '');

        $hotelResponse->setMealPlansAvailable(implode(', ', $this->meal_plans_available));

        $hotelResponse->setRoomGroups($roomGroups);

        if ($this->rate_type === ItemTypeEnum::COMPLETE->value) {
            $hotelResponse->setRoomCombinations($this->roomCombinations);
            $this->roomCombinations = [];
        }

        $hotelResponse->setLowestPricedRoomGroup($lowestPrice != 100000 ? $lowestPrice : '');

        return $hotelResponse->toArray();
    }

    private function fetchCountRefundableRates(array $propertyGroup): array
    {
        $refundableRates = [];
        $nonRefundableRates = [];
        foreach ($propertyGroup['rooms'] as $rate) {
            if ($rate['non_refundable']) {
                $nonRefundableRates[] = $rate['rate_id'];
            } else {
                $refundableRates[] = $rate['rate_id'];
            }
        }

        return ['refundable_rates' => implode(',', $refundableRates), 'non_refundable_rates' => implode(',', $nonRefundableRates)];
    }

    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup, int|string $supplierHotelId): array
    {
        $giataId = $propertyGroup['giata_id'] ?? 0;

        $roomGroupsResponse = RoomGroupsResponseFactory::create();
        $roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
        $roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');

        $roomGroupsResponse->setRateDescription($roomGroup['RoomRateDescription'] ?? '');
        $roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');

        $currency = Arr::get($roomGroup, 'rates.Total.@attributes.CurrencyCode');

        if ($currency === null) {
            $currency = Arr::get($roomGroup, 'rates.0.Total.@attributes.CurrencyCode');
        }

        $this->currency = $currency ?? 'USD';
        $roomGroupsResponse->setCurrency($this->currency);

        $rooms = [];
        $priceRoomData = [];
        foreach ($roomGroup['rates'] as $key => $room) {
            $roomData = $this->setRoomResponse((array)$room, $propertyGroup, $giataId, $supplierHotelId);
            $roomResponse = $roomData['roomResponse'];
            $pricingRulesApplierRoom = $roomData['pricingRulesApplier'];
            $rooms[] = $roomResponse;
            $priceRoomData[$key] = $pricingRulesApplierRoom;
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

        // return lowest priced room data
        $roomGroupsResponse->setTotalPrice($priceRoomData[$keyLowestPricedRoom]['total_price'] ?? 0.0);
        $roomGroupsResponse->setTotalTax($priceRoomData[$keyLowestPricedRoom]['total_tax'] ?? 0.0);
        $roomGroupsResponse->setTotalFees($priceRoomData[$keyLowestPricedRoom]['total_fees'] ?? 0.0);
        $roomGroupsResponse->setTotalNet($priceRoomData[$keyLowestPricedRoom]['total_net'] ?? 0.0);
        $roomGroupsResponse->setMarkup($priceRoomData[$keyLowestPricedRoom]['markup'] ?? 0.0);

        $roomGroupsResponse->setNonRefundable($rooms[$keyLowestPricedRoom]['non_refundable'] ?? false);
        $roomGroupsResponse->setRateId($rooms[$keyLowestPricedRoom]['rate_id'] ?? 0);
        $roomGroupsResponse->setCancellationPolicies($rooms[$keyLowestPricedRoom]['cancellation_policies'] ?? []);
        $roomGroupsResponse->setMealPlan($rooms[$keyLowestPricedRoom]['meal_plan'] ?? '');

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    public function setRoomResponse(array $rate, array $propertyGroup, int $giataId, int|string $supplierHotelId): array
    {
        $counts = [];
        foreach ($rate['GuestCounts']['GuestCount'] as $guestCount) {
            if (isset($guestCount['Age'])) {
                $counts[$guestCount['Age']] = $guestCount['Count'];
            } else {
                $counts[$guestCount['@attributes']['Age']] = $guestCount['@attributes']['Count'];
            }
        }

        $adults = $children = $infants = $unknown = 0;
        $childrenAges = [];

        foreach ($counts as $age => $count) {
            if ($age < 0) {
                $adults += $count;
                $unknown += $count;
            } elseif ($age < HbsiClient::AGE_INFANT) {
                $infants += $count;
                $childrenAges[] = $age;
            } elseif ($age < HbsiClient::AGE_CHILD) {
                $children += $count;
                $childrenAges[] = $age;
            } else {
                $adults += $count;
            }
        }

        // TODO: fix room_combinations.
        /*
         * Please consider adding $unknown elsewhere.
         * $rateOccupancy (“rate_occupancy”) is used to combine room_combinations in RS prising_search.
         * Adding $unknown causes room_combinations generation conflicts for multi-room search
         */
//        $rateOccupancy = $adults.'-'.$children.'-'.$infants.'-'.$unknown;
        $rateOccupancy = $adults . '-' . $children . '-' . $infants;

        $rateOrdinal = $rate['rate_ordinal'] ?? 0;

        $roomType = $rate['RoomTypes']['RoomType']['@attributes']['RoomTypeCode'] ?? '';

        // enrichment Pricing Rules / Application of Pricing Rules
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['markup'] = 0.0;
        try {
            $rateToApply['Rates'] = $rate['RoomRates']['RoomRate']['Rates'];
            $rateToApply['rateOccupancy'] = $rateOccupancy;
            $pricingRulesApplier = $this->pricingRulesApplier->apply(
                $giataId,
                $rateToApply,
                $rate['RatePlans']['RatePlan']['RatePlanDescription']['@attributes']['Name'] ?? '',
                $rateOccupancy,
                $roomType,
            );
        } catch (Exception $e) {
            Log::error('HbsiHotelPricingDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());
        }

        $cancellationPolicies = [];
        $cancellationPoliciesInput = [];
        $nonRefundable = false;
        $penaltyDate = null;

        if (isset($rate['CancelPenalties'])) {
            if (isset($rate['CancelPenalties']['CancelPenalty']['@attributes'])) {
                $cancellationPoliciesInput[] = $rate['CancelPenalties']['CancelPenalty'];
            } else {
                foreach ($rate['CancelPenalties']['CancelPenalty'] as $item) {
                    $cancellationPoliciesInput[] = $item;
                }
            }
            foreach ($cancellationPoliciesInput as $cancelPenalty) {
                $data = [];

                $policy = PolicyCode::fromCode(Arr::get($cancelPenalty, '@attributes.PolicyCode', PolicyCode::CXP->name));
                $data['description'] = $policy->value;
                $data['type'] = PolicyCode::getObeCode($policy)->value;

                if (isset($cancelPenalty['Deadline']['@attributes']['AbsoluteDeadline'])) {
                    $absoluteDeadline = $cancelPenalty['Deadline']['@attributes']['AbsoluteDeadline'];
                    $data['penalty_start_date'] = date('Y-m-d', strtotime($absoluteDeadline));

                    if ($data['penalty_start_date'] <= date('Y-m-d')) {
                        $nonRefundable = true;
                    }

                    if ($policy === PolicyCode::CXP && ($penaltyDate === null || $penaltyDate > $data['penalty_start_date'])) {
                        $penaltyDate = $data['penalty_start_date'];
                    }
                }
                if (isset($cancelPenalty['AmountPercent']['@attributes']['Percent'])) {
                    $data['percentage'] = $cancelPenalty['AmountPercent']['@attributes']['Percent'];
                }
                if (isset($cancelPenalty['AmountPercent']['@attributes']['Amount'])) {
                    $data['amount'] = $cancelPenalty['AmountPercent']['@attributes']['Amount'];
                }
                if (isset($cancelPenalty['AmountPercent']['@attributes']['CurrencyCode'])) {
                    $data['currency'] = $cancelPenalty['AmountPercent']['@attributes']['CurrencyCode'];
                }
                if (isset($cancelPenalty['AmountPercent']['@attributes']['NmbrOfNights'])) {
                    $data['nights'] = $cancelPenalty['AmountPercent']['@attributes']['NmbrOfNights'];
                }

                $cancellationPolicies[] = $data;
            }
        }

        if ($penaltyDate === null) {
            $penaltyDate = date('Y-m-d');

            $cancellationPolicies[] = [
                'description' => PolicyCode::CXP->value,
                'type' => CancellationPolicyTypesEnum::General->value,
                'penalty_start_date' => $penaltyDate,
                'percentage' => '100',
            ];

            $nonRefundable = true;
        }

        $giataCode = Arr::get($propertyGroup, 'giata_id',0);
        $roomType = Arr::get($rate, 'RoomTypes.RoomType.@attributes.RoomTypeCode', 0);

        $roomName = Arr::get($this->mapperSupplierRepository, "$giataCode.$roomType.name", $rate['RoomTypes']['RoomType']['RoomDescription']['@attributes']['Name'] ?? '');
        $roomDescription = is_array($rate['RoomTypes']['RoomType']['RoomDescription']['Text'])
            ? implode(' ', $rate['RoomTypes']['RoomType']['RoomDescription']['Text'])
            : $rate['RoomTypes']['RoomType']['RoomDescription']['Text'] ?? '';
        $roomDescription = Arr::get($this->mapperSupplierRepository, "$giataCode.$roomType.description", $roomDescription);

        $roomResponse = RoomResponseFactory::create();
        $roomResponse->setGiataRoomCode($rate['giata_room_code'] ?? '');
        $roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
        $roomResponse->setPenaltyDate($penaltyDate);
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($roomName);
        $roomResponse->setSupplierRoomCode($rateOccupancy);
        $roomResponse->setCapacity([
            'adults' => $adults - $unknown,
            'children' => $childrenAges,
            'unknown' => $unknown,
        ]);

        $roomResponse->setSupplierBedGroups($rate['bed_groups'] ?? 0);
        $roomResponse->setRoomType($roomType);
        $roomResponse->setRoomDescription($roomDescription);
        $roomResponse->setRateName($rate['RatePlans']['RatePlan']['RatePlanDescription']['@attributes']['Name'] ?? '');
        if (is_string($rate['RatePlans']['RatePlan']['RatePlanDescription']['Text'])) {
            $roomResponse->setRateDescription($rate['RatePlans']['RatePlan']['RatePlanDescription']['Text']);
        }
        $roomResponse->setRateId($rateOrdinal);
        $roomResponse->setRatePlanCode($rate['RatePlans']['RatePlan']['@attributes']['RatePlanCode'] ?? '');
        $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
        $roomResponse->setTotalTax($pricingRulesApplier['total_tax']);
        $roomResponse->setTotalFees($pricingRulesApplier['total_fees']);
        $roomResponse->setTotalNet($pricingRulesApplier['total_net']);
        $roomResponse->setMarkup($pricingRulesApplier['markup']);
        $roomResponse->setCurrency($this->currency ?? 'USD');
        $roomResponse->setCancellationPolicies($cancellationPolicies);
        $roomResponse->setNonRefundable($nonRefundable);
        $mealPlanCode = $rate['RatePlans']['RatePlan']['MealsIncluded']['@attributes']['MealPlanCodes'] ?? '';
        $mealPlanName = self::MEAL_PLAN[$mealPlanCode] ?? '';
        $roomResponse->setMealPlans($mealPlanName);

        if (!in_array($mealPlanName, $this->meal_plans_available)) {
            $this->meal_plans_available[] = $mealPlanName;
        }

        $roomResponse->setBreakdown($this->getBreakdown($rateToApply));

        $bookingItem = Str::uuid()->toString();
        $roomResponse->setBookingItem($bookingItem);

        $booking_pricing_data = $roomResponse->toArray();
        $booking_pricing_data['rate_description'] = mb_substr($booking_pricing_data['rate_description'], 0, 200, 'UTF-8');

        $this->roomCombinations[$bookingItem] = [$bookingItem];

        $this->bookingItems[] = [
            'booking_item' => $bookingItem,
            'supplier_id' => $this->supplier_id,
            'search_id' => $this->search_id,
            'booking_item_data' => json_encode([
                'hotel_id' => $propertyGroup['giata_id'] ?? 0,
                'hotel_supplier_id' => $supplierHotelId,
                'rate_occupancy' => $rateOccupancy,
                'rate_type' => $this->rate_type,
                'room_id' => $rate['id'] ?? $roomType ?? 0,
                'rate_ordinal' => $rate['id'] ?? $rateOrdinal ?? 0,
                'bed_groups' => '',
            ]),
            'rate_type' => $this->rate_type,
            'booking_pricing_data' => json_encode($booking_pricing_data),
            'created_at' => Carbon::now(),
            'hotel_id' => $propertyGroup['giata_id'] ?? 0,
            'room_id' => $rate['id'] ?? $roomType ?? 0,
        ];

        return ['roomResponse' => $roomResponse->toArray(), 'pricingRulesApplier' => $pricingRulesApplier];
    }

    private function getBreakdown(array $rates): array
    {
        $breakdown = [];
        $fees = [];
        $night = 0;
        if (isset($rates['Rates']['Rate']) && is_numeric(array_key_first($rates['Rates']['Rate']))) {
            $loopRates = $rates['Rates']['Rate'];
        } else {
            $loopRates[] = $rates['Rates']['Rate'];
        }
        foreach ($loopRates as $rate) {
            // check if AmountBeforeTax is equal to AmountAfterTax
            //if ($rate['Base']['@attributes']['AmountBeforeTax'] == $rate['Base']['@attributes']['AmountAfterTax']) continue;

            $nightsRate = $rate['@attributes']['UnitMultiplier'];
            $baseFareRate = [
                'amount' => $rate['Base']['@attributes']['AmountBeforeTax'],
                'title' => 'Base Rate',
                'type' => 'base_rate',
            ];

            $totals = [
                'total_inclusive' => 0,
                'total_exclusive' => 0,
            ];

            $taxesFeesRate = [];
            if (isset($rate['Base']['Taxes'])) {
                $taxes = $rate['Base']['Taxes'];
                foreach ($taxes as $tax) {
                    $_taxes = $tax;

                    if (Arr::has($tax, '@attributes')) {
                        $_taxes = [$tax];
                    }

                    foreach ($_taxes as $_tax) {
                        $type = null;
                        $code = strtolower($_tax['@attributes']['Code']);
                        $name = strtolower($_tax['@attributes']['Type']);
                        if (in_array(strtolower($_tax['@attributes']['Code']), $this->fees) || $_tax['@attributes']['Type'] === 'PropertyCollects') {
                            $type = 'fee';
                        }
                        if (in_array($code, $this->taxes)) {
                            $type = 'tax';
                        }

                        if($type !== 'fee') {
                            $taxesFeesRate[] = [
                                'type' => $type ?? 'tax' . ' ' . $name,
                                'amount' => $_tax['@attributes']['Amount'],
                                'title' => Arr::get($_tax, 'TaxDescription.Text', isset($_tax['@attributes']['Percent'])
                                    ? $_tax['@attributes']['Percent'] . ' % ' . $_tax['@attributes']['Code']
                                    : $_tax['@attributes']['Code']),
                            ];
                        }else{
                            $fees[] = [
                                'type' => $type ?? 'tax' . ' ' . $name,
                                'amount' => $_tax['@attributes']['Amount'],
                                'title' => Arr::get($_tax, 'TaxDescription.Text', isset($_tax['@attributes']['Percent'])
                                    ? $_tax['@attributes']['Percent'] . ' % ' . $_tax['@attributes']['Code']
                                    : $_tax['@attributes']['Code']),
                            ];
                        }

                        $taxType = strtolower($_tax['@attributes']['Type']);

                        if (Arr::has($totals, "total_$taxType")) {
                            $totals["total_$taxType"] += $_tax['@attributes']['Amount'];
                        }
                    }
                }
            }

            if ($rate['Base']['@attributes']['AmountBeforeTax'] === $rate['Base']['@attributes']['AmountAfterTax'] && $totals['total_inclusive'] > 0) {
                $baseFareRate['amount'] -= $totals['total_inclusive'];
            }

            for ($i = 0; $i < $nightsRate; $i++) {
                $breakdown[$night][] = $baseFareRate;
                $breakdown[$night] = array_merge($breakdown[$night], $taxesFeesRate);
                $night++;
            }

        }

        $breakdownWithoutKeys = [];
        foreach ($breakdown as $item) {
            $breakdownWithoutKeys[] = array_values($item);
        }

        return [
            'nightly' => $breakdownWithoutKeys,
            'stay' => [],
            'fees' => $fees,

        ];
    }
}
