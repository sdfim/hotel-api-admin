<?php

namespace Modules\API\Suppliers\Transformers\HBSI;

use App\Models\Supplier;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\Resolvers\Deposits\DepositResolver;
use Modules\API\PricingAPI\Resolvers\TaxAndFees\TaxAndFeeResolver;
use Modules\API\PricingAPI\Resolvers\UltimateAmenities\UltimateAmenityResolver;
use Modules\API\PricingAPI\ResponseModels\HotelResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomResponseFactory;
use Modules\API\PricingRules\HBSI\HbsiPricingRulesApplier;
use Modules\API\Suppliers\Enums\CancellationPolicyTypesEnum;
use Modules\API\Suppliers\Enums\HBSI\PolicyCode;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Suppliers\Transformers\BaseHotelPricingTransformer;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class HbsiHotelPricingTransformer extends BaseHotelPricingTransformer
{
    private HbsiPricingRulesApplier $pricingRulesApplier;

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
        private readonly TaxAndFeeResolver $taxAndFeeResolver,
        private readonly UltimateAmenityResolver $ultimateAmenityResolver,
        private array $meal_plans_available = [],
        private array $roomCombinations = [],
        private array $basicHotelData = [],
        private string $rate_type = '',
        private string $currency = '',
        private int $supplier_id = 0,
    ) {}

    public function HbsiToHotelResponse(array $supplierResponse, array $query, string $search_id, array $pricingRules, array $pricingExclusionRules, array $giataIds): array
    {
        $this->initializePricingData($query, $pricingExclusionRules, $giataIds, $search_id);
        $this->fetchSupplierRepositoryData($search_id, $giataIds);

        $this->rate_type = count($query['occupancy']) > 1 ? ItemTypeEnum::SINGLE->value : ItemTypeEnum::COMPLETE->value;
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;

        $pricingRules = array_column($pricingRules, null, 'property');
        $this->pricingRulesApplier = new HbsiPricingRulesApplier($query, $pricingRules);

        $hotelResponse = [];
        foreach ($supplierResponse as $key => $propertyGroup) {
            if (! in_array($propertyGroup['giata_id'], $query['filtered_giata_ids'])) {
                continue;
            }
            $hotelResponse[] = $this->setHotelResponse($propertyGroup, $key, $query);
        }

        return ['response' => $hotelResponse, 'bookingItems' => $this->bookingItems];
    }

    public function setHotelResponse(array $propertyGroup, int|string $key, array $query): array
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
        $rating = $this->giata[$propertyGroup['giata_id']]['rating'] ?? 0;
        $hotelResponse->setDepositInformation(DepositResolver::getHotelLevel(Arr::get($this->depositInformation, $propertyGroup['giata_id'], []), $query, $propertyGroup['giata_id'], $rating));

        $hotelResponse->setPayAtHotelAvailable($propertyGroup['pay_at_hotel_available'] ?? '');
        $hotelResponse->setPayNowAvailable($propertyGroup['pay_now_available'] ?? '');

        $roomGroups = [];
        $lowestPrice = 100000;

        foreach ($propertyGroup['rooms'] as $roomGroup) {
            $roomGroupsData = $this->setRoomGroupsResponse($roomGroup, $propertyGroup, $key, $query);
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

    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup, int|string $supplierHotelId, array $query): array
    {
        $giataId = $propertyGroup['giata_id'] ?? 0;

        $basicHotelData = Arr::get($this->basicHotelData, $propertyGroup['giata_id']);

        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');

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
            $ratePlanCode = Arr::get($room, 'RatePlans.RatePlan.@attributes.RatePlanCode', '');
            // exclude rate codes from the response according to excludeRules
            if (in_array($ratePlanCode, $this->exclusionRates)) {
                continue;
            }

            $roomData = $this->setRoomResponse((array) $room, $propertyGroup, $giataId, $supplierHotelId, $query);
            if (empty($roomData)) {
                continue;
            }
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
        $roomGroupsResponse->setMarkup($isCommissionTracking ? 0 : ($priceRoomData[$keyLowestPricedRoom]['markup'] ?? 0.0));

        $roomGroupsResponse->setNonRefundable($rooms[$keyLowestPricedRoom]['non_refundable'] ?? false);
        $roomGroupsResponse->setRateId($rooms[$keyLowestPricedRoom]['rate_id'] ?? 0);
        $roomGroupsResponse->setCancellationPolicies($rooms[$keyLowestPricedRoom]['cancellation_policies'] ?? []);
        $roomGroupsResponse->setMealPlan($rooms[$keyLowestPricedRoom]['meal_plan'] ?? '');

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    public function setRoomResponse(array $rate, array $propertyGroup, int $giataId, int|string $supplierHotelId, array $query): array
    {
        $roomType = Arr::get($rate, 'RoomTypes.RoomType.@attributes.RoomTypeCode', 0);
        $giataCode = Arr::get($propertyGroup, 'giata_id', 0);
        $roomName = Arr::get($this->mapperSupplierRepository, "$giataCode.$roomType.name", $rate['RoomTypes']['RoomType']['RoomDescription']['@attributes']['Name'] ?? '');
        // exclude room types and names from the response according to excludeRules
        if (in_array($roomType, $this->exclusionRoomTypes) || in_array($roomName, $this->exclusionRoomNames)) {
            return [];
        }

        $basicHotelData = Arr::get($this->basicHotelData, $giataId);
        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');
        $ratePlanCode = Arr::get($rate, 'RatePlans.RatePlan.@attributes.RatePlanCode', '');

        $hbsiUnifiedRoomCodes = Arr::get($this->unifiedRoomCodes, ContentSourceEnum::HBSI->value, []);
        $unifiedRoomCode = Arr::get($hbsiUnifiedRoomCodes, "$giataCode.$roomType", '');

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

        /*
         * Please consider adding $unknown elsewhere.
         * $rateOccupancy (“rate_occupancy”) is used to combine room_combinations in RS prising_search.
         * Adding $unknown causes room_combinations generation conflicts for multi-room search
         */
        //        $rateOccupancy = $adults.'-'.$children.'-'.$infants.'-'.$unknown;
        $rateOccupancy = $adults.'-'.$children.'-'.$infants;
        $rateOrdinal = $rate['rate_ordinal'] ?? 0;
        $numberOfPassengers = $adults + $children + $infants;

        // enrichment Pricing Rules / Application of Pricing Rules
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['markup'] = 0.0;

        $rateToApply = [];

        $rateToApply['Rates'] = $rate['RoomRates']['RoomRate']['Rates'];
        $rateToApply['rateOccupancy'] = $rateOccupancy;
        $transformedRates = $this->taxAndFeeResolver->transformRates($rateToApply['Rates']);
        $this->taxAndFeeResolver->applyRepoTaxFees($transformedRates, $giataId, $ratePlanCode, $unifiedRoomCode, $numberOfPassengers, $this->checkin, $this->checkout, $this->repoTaxFees);

        $rateToApply['transformedRates'] = $transformedRates;

        if (config('booking-suppliers.HBSI.use_debug_tax_fee')) {
            \Log::debug('transformedRates | giataId '.$giataId.' | unifiedRoomCode '.$unifiedRoomCode.' | rateCode '.$ratePlanCode, [
                'transformedRates' => $transformedRates,
                'repoTaxFees ' => $this->repoTaxFees,
            ]);
        }

        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply(
                $giataId,
                $rateToApply,
                $rate['RatePlans']['RatePlan']['RatePlanDescription']['@attributes']['Name'] ?? '',
                $rateOccupancy,
                $rate['RoomTypes']['RoomType']['@attributes']['RoomTypeCode'] ?? '',
                $rate['RatePlans']['RatePlan']['@attributes']['RatePlanCode'] ?? '',
            );
        } catch (Exception $e) {
            Log::error('HbsiHotelPricingTransformer | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
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
        $roomResponse->setUnifiedRoomCode($unifiedRoomCode);
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
        $roomResponse->setRatePlanCode($ratePlanCode);

        $roomUltimateAmenities = collect($this->ultimateAmenityResolver->resolve(
            $roomResponse, Arr::get($this->ultimateAmenities, $propertyGroup['giata_id'], []), $query
        ))->filter(function ($amenity) use ($unifiedRoomCode) {
            return (empty($amenity['drivers']) || in_array(SupplierNameEnum::HBSI->value, $amenity['drivers'], true))
                && (empty($amenity['priority_rooms']) || in_array($unifiedRoomCode, $amenity['priority_rooms'], true));
        })->map(function ($amenity) {
            unset($amenity['drivers']);
            unset($amenity['priority_rooms']);

            return $amenity;
        })->toArray();
        $roomUltimateAmenities = array_values($roomUltimateAmenities);
        $feesUltimateAmenities = $this->ultimateAmenityResolver->getFeesUltimateAmenities(
            $roomUltimateAmenities, $numberOfPassengers, $this->checkin, $this->checkout);
        $totalFeesUltimateAmenities = $this->ultimateAmenityResolver->getTotalFeesAmount($feesUltimateAmenities) ?? 0.0;

        $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
        $roomResponse->setTotalTax($pricingRulesApplier['total_tax']);
        $roomResponse->setTotalFees($pricingRulesApplier['total_fees'] + $totalFeesUltimateAmenities);
        $roomResponse->setTotalNet($pricingRulesApplier['total_net']);

        $roomResponse->setMarkup($pricingRulesApplier['markup']);
        if ($isCommissionTracking) {
            $roomResponse->setMarkup(0);
            $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
            $roomResponse->setCommissionAmount($pricingRulesApplier['markup']);
        }
        $roomResponse->setCommissionableAmount($roomResponse->getTotalPrice() + $roomResponse->getMarkup() - $roomResponse->getTotalTax());
        $roomResponse->setCurrency($this->currency ?? 'USD');
        $roomResponse->setCancellationPolicies($cancellationPolicies);
        $roomResponse->setNonRefundable($nonRefundable);
        $mealPlanCode = $rate['RatePlans']['RatePlan']['MealsIncluded']['@attributes']['MealPlanCodes'] ?? '';
        $mealPlanName = self::MEAL_PLAN[$mealPlanCode] ?? '';
        $roomResponse->setMealPlans($mealPlanName);

        if (! in_array($mealPlanName, $this->meal_plans_available)) {
            $this->meal_plans_available[] = $mealPlanName;
        }

        $roomResponse->setAmenities($roomUltimateAmenities);

        if (config('supplier-repository.use_repo_tax_fees')) {
            $breakdown = $this->taxAndFeeResolver->getTransformedBreakdown($rateToApply['transformedRates'], $this->fees);
        } else {
            $breakdown = $this->getBreakdown($rateToApply);
        }

        $breakdown['fees'] = array_merge($breakdown['fees'], array_values($feesUltimateAmenities));
        $roomResponse->setBreakdown($breakdown);

        $bookingItem = Str::uuid()->toString();
        $roomResponse->setBookingItem($bookingItem);

        $roomResponse->setPricingRulesAppliers($this->transformPricingRulesAppliers($pricingRulesApplier));

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
            'cache_checkpoint' => Arr::get($propertyGroup, 'giata_id', 0).':'.$roomType,
        ];
        $rating = Arr::get($this->giata, "$giataId.rating", 0);
        $roomResponse->setDeposits(DepositResolver::getRateLevel($roomResponse, Arr::get($this->depositInformation, $giataId, []), $query, $giataId, $rating, Arr::get($pricingRulesApplier, 'total_price', 0)));

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
            // if ($rate['Base']['@attributes']['AmountBeforeTax'] == $rate['Base']['@attributes']['AmountAfterTax']) continue;

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

                        if ($type !== 'fee') {
                            $taxesFeesRate[] = [
                                'type' => $type ?? 'tax'.' '.$name,
                                'amount' => $_tax['@attributes']['Amount'],
                                'title' => Arr::get($_tax, 'TaxDescription.Text', isset($_tax['@attributes']['Percent'])
                                    ? $_tax['@attributes']['Percent'].' % '.$_tax['@attributes']['Code']
                                    : $_tax['@attributes']['Code']),
                            ];
                        } else {
                            $fees[] = [
                                'type' => $type ?? 'tax'.' '.$name,
                                'amount' => $_tax['@attributes']['Amount'],
                                'title' => Arr::get($_tax, 'TaxDescription.Text', isset($_tax['@attributes']['Percent'])
                                    ? $_tax['@attributes']['Percent'].' % '.$_tax['@attributes']['Code']
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
