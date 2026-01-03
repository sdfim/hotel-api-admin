<?php

namespace Modules\API\Suppliers\Oracle\Transformers;

use App\Models\Supplier;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\Resolvers\Deposits\DepositResolver;
use Modules\API\PricingAPI\Resolvers\DescriptiveContent\DescriptiveContentResolver;
use Modules\API\PricingAPI\Resolvers\MealPlan\MealPlanResolver;
use Modules\API\PricingAPI\Resolvers\Services\ServiceResolver;
use Modules\API\PricingAPI\ResponseModels\HotelResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponseFactory;
use Modules\API\PricingRules\PricingRulesApplier;
use Modules\API\Suppliers\Base\Transformers\BaseHotelPricingTransformer;
use Modules\API\Suppliers\Oracle\Resolvers\OracleTaxAndFeeResolver;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class OracleHotelPricingTransformer extends BaseHotelPricingTransformer
{
    private PricingRulesApplier $pricingRulesApplier;

    public array $bookingItems = [];

    public function __construct(
        private readonly OracleTaxAndFeeResolver $taxAndFeeResolver,
        private readonly ServiceResolver $serviceResolver,
        private readonly MealPlanResolver $mealPlanResolver,
        private array $meal_plans_available = [],
        private array $roomCombinations = [],
        private string $rate_type = '',
        private string $currency = '',
        private int $supplier_id = 0,
    ) {}

    public function OracleToHotelResponse(array $supplierResponse, array $query, string $search_id, array $pricingRules, array $pricingExclusionRules, array $giataIds): \Generator
    {
        $this->initializePricingData($query, $pricingExclusionRules, $giataIds, $search_id);
        $this->fetchSupplierRepositoryData($search_id, $giataIds);

        $this->rate_type = count($query['occupancy']) > 1 ? ItemTypeEnum::SINGLE->value : ItemTypeEnum::COMPLETE->value;
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::ORACLE->value)->first()->id;

        $pricingRules = array_column($pricingRules, null, 'property');
        $this->pricingRulesApplier = new PricingRulesApplier($query, $pricingRules);

        foreach ($supplierResponse as $key => $propertyGroup) {
            yield $this->setHotelResponse($propertyGroup, $key, $query);
        }
    }

    public function setHotelResponse(array $propertyGroup, int|string $key, array $query): array
    {
        $this->roomCombinations = [];
        $giataId = $propertyGroup['giata_id'] ?? 0;
        $hotelResponse = HotelResponseFactory::create();
        $hotelResponse->setGiataHotelId($giataId);
        $hotelResponse->setDistanceFromSearchLocation($this->giata[$giataId]['distance'] ?? 0);
        $hotelResponse->setRating($this->getAttributeFromHotelOrProduct($giataId, 'rating'));
        $hotelResponse->setHotelName($this->giata[$giataId]['hotel_name'] ?? '');
        $hotelResponse->setBoardBasis(($propertyGroup['board_basis'] ?? ''));
        $hotelResponse->setSupplier(SupplierNameEnum::ORACLE->value);
        $hotelResponse->setSupplierHotelId($key);
        $hotelResponse->setDestination($this->giata[$giataId]['city'] ?? $this->destinationData);
        $hotelResponse->setCommissions($this->commissions[$giataId] ?? []);

        $descriptiveContent = DescriptiveContentResolver::getHotelLevel(Arr::get($this->descriptiveContent, $giataId, []), $query, $giataId);
        $hotelResponse->setDescriptiveContent($descriptiveContent);

        $hotelResponse->setPayAtHotelAvailable($propertyGroup['pay_at_hotel_available'] ?? '');
        $hotelResponse->setPayNowAvailable($propertyGroup['pay_now_available'] ?? '');

        $hotelResponse->setHoldable($this->features[$giataId]['holdable'] ?? true);

        $roomGroups = [];
        $lowestPrice = 100000;

        $supplierRateData = $propertyGroup['ratePlans'] ?? [];

        foreach ($propertyGroup['rooms'] as $roomGroup) {
            $roomGroupsData = $this->setRoomGroupsResponse($roomGroup, $propertyGroup, $key, $query, $supplierRateData);
            if (empty($roomGroupsData)) {
                continue;
            }
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

        $hotelResponse->setHotelContacts([]);

        $hotelResponse->setInformativeFees(
            $this->taxAndFeeResolver->getInformativeFeesHotelLevel(
                informativeFees: $this->informativeFees,
                giataId: $giataId,
                supplierId: $this->supplier_id,
                checkin: $this->checkin ?? null,
                checkout: $this->checkout ?? null,
                occupancy: $this->occupancy ?? [],
            )
        );

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

    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup, int|string $supplierHotelId, array $query, array $supplierRateData): array
    {
        $giataId = $propertyGroup['giata_id'] ?? 0;

        $basicHotelData = Arr::get($this->basicHotelData, $giataId);

        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');

        $firstRateName = Arr::get($roomGroup, 'rates.0.ratePlanCode', '');
        $payDates = $this->determineRatePaymentTerms(Arr::get($supplierRateData, $firstRateName, []));

        $roomGroupsResponse = RoomGroupsResponseFactory::create();
        $roomGroupsResponse->setPayNow($payDates['pay_now'] ?? '');
        $roomGroupsResponse->setPayAtHotel($payDates['pay_at_hotel'] ?? '');

        $roomGroupsResponse->setRateDescription($roomGroup['room_name'] ?? '');
        $roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');

        $this->currency = Arr::get($roomGroup, 'rates.0.total.currencyCode', 'USD');
        $roomGroupsResponse->setCurrency($this->currency);

        $rooms = [];
        $priceRoomData = [];
        $key = 0;
        foreach ($roomGroup['rates'] as $room) {
            $ratePlanCode = Arr::get($room, 'ratePlanCode', '');
            // exclude rate codes from the response according to excludeRules
            if (in_array($ratePlanCode, $this->exclusionRates)) {
                continue;
            }

            $roomData = $this->setRoomResponse((array) $room, $propertyGroup, $giataId, $supplierHotelId, $query, $supplierRateData);
            if (empty($roomData)) {
                continue;
            }
            $roomResponse = $roomData['roomResponse'];
            $pricingRulesApplierRoom = $roomData['pricingRulesApplier'];
            $rooms[] = $roomResponse;
            $priceRoomData[$key] = $pricingRulesApplierRoom;
            $key++;
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
        $roomGroupsResponse->setTotalPrice(round($priceRoomData[$keyLowestPricedRoom]['total_price'] ?? 0.0, 2));
        $roomGroupsResponse->setTotalTax(round($priceRoomData[$keyLowestPricedRoom]['total_tax'] ?? 0.0, 2));
        $roomGroupsResponse->setTotalFees(round($priceRoomData[$keyLowestPricedRoom]['total_fees'] ?? 0.0, 2));
        $roomGroupsResponse->setTotalNet(round($priceRoomData[$keyLowestPricedRoom]['total_net'] ?? 0.0, 2));

        $roomGroupsResponse->setNonRefundable($rooms[$keyLowestPricedRoom]['non_refundable'] ?? false);
        $roomGroupsResponse->setRateId($rooms[$keyLowestPricedRoom]['rate_id'] ?? 0);
        $roomGroupsResponse->setCancellationPolicies($rooms[$keyLowestPricedRoom]['cancellation_policies'] ?? []);

        /** @var RoomResponse $roomResponse */
        $roomResponse = app(RoomResponse::class);
        $roomResponseLowestPrice = $roomResponse->fromArray($rooms[$keyLowestPricedRoom]);
        $roomGroupsResponse->setDeposits($roomResponseLowestPrice->getDeposits());

        $roomGroupsResponse->setMealPlan($rooms[$keyLowestPricedRoom]['meal_plan'] ?? '');

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    public function setRoomResponse(array $rate, array $propertyGroup, int $giataId, int|string $supplierHotelId, array $query, array $supplierRateData): array
    {
        $roomType = Arr::get($rate, 'room_code', 0);
        $giataCode = Arr::get($propertyGroup, 'giata_id', 0);

        $oracleUnifiedRoomCodes = Arr::get($this->unifiedRoomCodes, ContentSourceEnum::ORACLE->value, []);
        $unifiedRoomCode = Arr::get($oracleUnifiedRoomCodes, "$giataCode.$roomType", '');
        $srRoomId = Arr::get($this->roomIdByUnifiedCode, "$giataCode.$unifiedRoomCode", '');

        if ($unifiedRoomCode) {
            $roomName = Arr::get($this->mapperSupplierRepository, "$giataCode.$unifiedRoomCode.name", $rate['room_name'] ?? '');
        } else {
            $roomName = Arr::get($this->mapperSupplierRepository, "$giataCode.$roomType.name", $rate['room_name'] ?? '');
        }

        // exclude room types and names from the response according to excludeRules
        if (in_array($roomType, $this->exclusionRoomTypes) || in_array($roomName, $this->exclusionRoomNames)) {
            return [];
        }

        $basicHotelData = Arr::get($this->basicHotelData, $giataId);
        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');
        $ratePlanCode = Arr::get($rate, 'rate_plan_code', '');

        $oracleUnifiedRoomCodes = Arr::get($this->unifiedRoomCodes, ContentSourceEnum::ORACLE->value, []);
        $unifiedRoomCode = Arr::get($oracleUnifiedRoomCodes, "$giataCode.$roomType", '');

        $passengers = $this->calculateTotalGuestsPerRoom($this->occupancy);

        $rateOrdinal = $rate['rate_ordinal'] ?? 0;
        $numberOfPassengers = $passengers[$rate['room_key'] ?? 0] ?? 1;

        // enrichment Pricing Rules / Application of Pricing Rules
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;

        $currency = Arr::get($rate, 'total.currencyCode', 'USD');

        $repoTaxFees = collect(Arr::get($this->repoTaxFees, $giataId, []))
            ->map(function ($items) {
                return collect($items)->filter(function ($item) {
                    return $item['supplier_name'] === null
                        || $item['supplier_name'] === ContentSourceEnum::ORACLE->value;
                })->all();
            })
            ->filter(fn ($items) => ! empty($items))
            ->all();
        $transformedRates = $this->taxAndFeeResolver->transformRates($rate, $repoTaxFees);
        $this->taxAndFeeResolver->applyRepoTaxFees($transformedRates, $numberOfPassengers, $this->checkin, $this->checkout, $repoTaxFees, $this->occupancy, $currency);
        $this->serviceResolver->applyRepoService($transformedRates, $giataId, $ratePlanCode, $unifiedRoomCode, $numberOfPassengers, $this->checkin, $this->checkout, $this->repoServices, $this->occupancy, $currency);

        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply(
                giataId: $giataId,
                transformedRates: $transformedRates,
                rateOccupancy: $numberOfPassengers,
                roomName: $rate['room_name'] ?? '',
                roomCode: $rate['room_code'] ?? '',
                roomType: $roomType,
                rateCode: $rate['rate_plan_code'] ?? '',
                srRoomId: $srRoomId,
            );
        } catch (Exception $e) {
            Log::error('OracleHotelPricingTransformer | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());
        }

        if ($pricingRulesApplier['total_price'] === 0.0) {
            return [];
        }

        $cancellationPolicies = [];
        $nonRefundable = false;
        $penaltyDate = null;

        $roomDescription = Arr::get($this->mapperSupplierRepository, "$giataCode.$roomType.description", '');

        $roomResponse = RoomResponseFactory::create();

        $roomResponse->setGiataRoomCode($srRoomId);
        $roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
        $roomResponse->setPenaltyDate($penaltyDate);
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($roomName);
        $roomResponse->setSupplierRoomCode($rate['room_key'] ?? '');
        $roomResponse->setUnifiedRoomCode($unifiedRoomCode);

        $roomResponse->setSupplierBedGroups($rate['bed_groups'] ?? 0);

        $bedGroups = Arr::get($this->mapperSupplierRepository, "$giataCode.$roomType.bed_groups", []);
        $roomResponse->setBedGroups($bedGroups);
        $roomResponse->setRoomType($roomType);
        $roomResponse->setRoomDescription($roomDescription);

        $rateInclusions = DescriptiveContentResolver::getRateInclusions(Arr::get($this->descriptiveContent, $giataId, []), $query, $ratePlanCode, $unifiedRoomCode);

        $rateDescription = '';
        if ($rateInclusions !== '') {
            $rateDescription = $rateInclusions;
        }

        $rateName = '';
        if (isset($this->rates[$giataId])) {
            foreach ($this->rates[$giataId] as $rateData) {
                if ($rateData['code'] === $ratePlanCode) {
                    $rateName = $rateData['name'];
                    break;
                }
            }
        }

        $ratePlanName = Arr::get($rate, 'rate_plan_code');

        if ($rateName == '' && is_string($ratePlanName)) {
            $rateName = $ratePlanName;
        }

        $ratePlanDescription = Arr::get($supplierRateData, "$ratePlanCode.description");

        if ($rateDescription == '' && is_string($ratePlanDescription)) {
            $rateDescription = $ratePlanDescription;
        }

        $roomResponse->setRateName($rateName ?? '');
        $roomResponse->setRateDescription($rateDescription);
        $roomResponse->setRateId($rateOrdinal);
        $roomResponse->setRatePlanCode($ratePlanCode);

        $roomResponse->setTotalPrice(round($pricingRulesApplier['total_price'], 2));
        $roomResponse->setTotalTax(round($pricingRulesApplier['total_tax'], 2));
        $roomResponse->setTotalFees(round($pricingRulesApplier['total_fees'], 2));
        $roomResponse->setTotalNet(round($pricingRulesApplier['total_net'], 2));

        if ($isCommissionTracking) {
            $roomResponse->setCommissionAmount(0.0);
        }
        $roomResponse->setCommissionableAmount(
            max(0.0, $roomResponse->getTotalPrice() - $roomResponse->getTotalTax())
        );
        $roomResponse->setCurrency($currency ?? 'USD');

        $roomResponse->setCancellationPolicies(array_values($cancellationPolicies));
        $roomResponse->setNonRefundable($nonRefundable);

        $roomResponse->setCancellationPolicies(array_values($cancellationPolicies));
        $roomResponse->setNonRefundable($nonRefundable);

        // --- Meal plan mapping (hotel-specific + DB overrides) ---

        // Raw value from supplier (could be a code like "AI" or a text like "No additional meals")
        // Force cast to string so that 0/1 are treated as "0"/"1", not as booleans
        $mealPlanRaw = '';

        // Try to resolve meal plan using DB mapping (pd_meal_plan_mappings)
        $mealPlanName = $this->mealPlanResolver->resolveMealPlanName(
            giataId: $giataId,
            mealPlanRaw: $mealPlanRaw,
            ratePlanCode: (string) $ratePlanCode, // just in case, also force string here
        );

        // Save meal plan to room response (single value per room/rate)
        $roomResponse->setMealPlans($mealPlanName);

        // Collect unique meal plans for hotel-level summary
        if ($mealPlanName !== '' && ! in_array($mealPlanName, $this->meal_plans_available, true)) {
            $this->meal_plans_available[] = $mealPlanName;
        }

        $roomResponse->setAmenities([]);

        $roomResponse->setInformativeFees($this->taxAndFeeResolver->getInformativeFeesRoomLevel(
            informativeFees: $this->informativeFees,
            giataId: $giataId,
            supplierId: $this->supplier_id,
            unifiedRoomCode: $unifiedRoomCode,
            RateCode: $ratePlanCode,
            occupancy: $this->occupancy ?? [],
            checkin: $this->checkin ?? null,
            checkout: $this->checkout ?? null
        ));

        $breakdown = $this->taxAndFeeResolver->getBreakdown($transformedRates, Carbon::parse($this->checkin), Carbon::parse($this->checkout), $currency);
        $roomResponse->setBreakdown($breakdown);

        $bookingItem = Str::uuid()->toString();
        $roomResponse->setBookingItem($bookingItem);

        $roomResponse->setPricingRulesAppliers($this->transformPricingRulesAppliers($pricingRulesApplier));

        $rating = Arr::get($this->giata, "$giataId.rating", 0);
        if (! ($roomResponse->getNonRefundable() ?? false)) {
            $roomResponse->setDeposits(DepositResolver::get(
                roomResponse: $roomResponse,
                depositInformation: Arr::get($this->depositInformation, $giataId, []),
                query: $query,
                giataId: $giataId,
                rating: $rating,
                roomCodes: $this->roomCodes,
                supplierName: SupplierNameEnum::ORACLE->value,
            ));
        } else {
            $roomResponse->setDeposits([]);
        }

        $dc = DescriptiveContentResolver::getRoomAndRateForRoomResponse(
            $roomResponse,
            Arr::get($this->descriptiveContent, $giataId, []),
            $query,
            $giataId,
            $unifiedRoomCode
        );
        $roomResponse->setDescriptiveContent($dc);

        $booking_pricing_data = $roomResponse->toArray();
        $booking_pricing_data['rate_description'] = mb_substr($booking_pricing_data['rate_description'], 0, 200, 'UTF-8');

        $this->roomCombinations[$bookingItem] = [$bookingItem];

        $this->bookingItems[] = [
            'booking_item' => $bookingItem,
            'supplier_id' => $this->supplier_id,
            'search_id' => $this->search_id,
            'booking_item_data' => json_encode([
                'hotel_id' => $giataId ?? 0,
                'hotel_name' => $this->giata[$giataId]['hotel_name'] ?? '',
                'hotel_supplier_id' => $supplierHotelId,
                'rate_occupancy' => $numberOfPassengers,
                'rate_type' => $this->rate_type,
                'room_id' => $rate['room_key'] ?? 0,
                'room_code' => $roomType,
                'rate_code' => $ratePlanCode,
                'supplier' => SupplierNameEnum::ORACLE->value,
                'rate_ordinal' => $rate['id'] ?? $rateOrdinal ?? 0,
                'bed_groups' => '',
            ]),
            'rate_type' => $this->rate_type,
            'booking_pricing_data' => json_encode($booking_pricing_data),
            'created_at' => Carbon::now()->toDateTimeString(),
            'cache_checkpoint' => Arr::get($propertyGroup, 'giata_id', 0).':'.$roomType,
        ];

        return ['roomResponse' => $roomResponse->toArray(), 'pricingRulesApplier' => $pricingRulesApplier];
    }

    /**
     * Determines payment and refund characteristics for an Oracle (OPERA) rate plan.
     *
     * * @param array $appliedGuaranteePolicy Array containing a single applied guarantee policy (resGuaranteeInfo).
     * @return array Returns an associative array with boolean flags:
     *               ['pay_now', 'pay_at_hotel', 'non_refundable'].
     */
    public function determineRatePaymentTerms(array $appliedGuaranteePolicy): array
    {
        // Initialize results
        $results = [
            'pay_now' => false,
            'pay_at_hotel' => false,
            'non_refundable' => false,
        ];

        // Safely extract guarantee code
        $guaranteeCode = strtoupper($appliedGuaranteePolicy['guarantee_code'] ?? '');

        // Safely extract deposit requirement
        $policyRequirements = $appliedGuaranteePolicy['policy_requirements'] ?? [];
        $requiresDeposit = $policyRequirements['deposit'] ?? false;

        // --- 1. Determine Non-Refundable status ---

        // Known non-refundable codes from OPERA/Oracle responses
        $nonRefundableCodes = ['NONREF', 'DNR', 'PREPAY'];

        if (in_array($guaranteeCode, $nonRefundableCodes)) {
            $results['non_refundable'] = true;
        }

        // --- 2. Determine Pay Now / Pay At Hotel status ---

        // Pay Now is True if:
        // 1. The rate is explicitly non-refundable.
        // 2. The policy requires a deposit (prepayment).
        if ($results['non_refundable'] || $requiresDeposit === true) {
            $results['pay_now'] = true;
        }

        // Pay At Hotel is True if payment is not required now.
        // This covers standard guarantees, corporate accounts (CXC), etc.
        if (! $results['pay_now']) {
            $results['pay_at_hotel'] = true;
        }

        return $results;
    }

    /**
     * Calculates the total number of guests (adults + children) for each room
     * in the provided occupancy array.
     *
     * @param  array  $occupancyArray  The array of room occupancy structures.
     * @return array An array where each element is the total count of guests for a room.
     */
    public function calculateTotalGuestsPerRoom(array $occupancyArray): array
    {
        $guestCounts = [];

        foreach ($occupancyArray as $index => $room) {
            $totalGuests = $room['adults'] ?? 0;
            $childrenCount = count($room['children_ages'] ?? []);
            $totalGuests += $childrenCount;

            $guestCounts["room_{$index}"] = $totalGuests;
        }

        return $guestCounts;
    }
}
