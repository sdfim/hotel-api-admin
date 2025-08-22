<?php

namespace Modules\API\Suppliers\Transformers\HotelTrader;

use App\Models\Supplier;
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
use Modules\API\PricingRules\HotelTrader\HotelTraderPricingRulesApplier;
use Modules\API\Suppliers\Transformers\BaseHotelPricingTransformer;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class HotelTraderHotelPricingTransformer extends BaseHotelPricingTransformer
{
    public array $bookingItems = [];

    public function __construct(
        private array $roomCombinations = [],
        private string $rate_type = '',
        private array $occupancies = [],
        private string $supplier_id = '',
    ) {}

    /**
     * Transforms HotelTrader data to hotel response format.
     */
    public function HotelTraderToHotelResponse(array $supplierResponse, array $query, string $search_id, array $pricingRules, array $pricingExclusionRules, array $giataIds): \Generator
    {
        $this->initializePricingData($query, $pricingExclusionRules, $giataIds, $search_id);
        $this->fetchSupplierRepositoryData($search_id, $giataIds);

        $this->rate_type = count($query['occupancy']) > 1 ? ItemTypeEnum::SINGLE->value : ItemTypeEnum::COMPLETE->value;
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()?->id;

        $this->pricingRulesApplier = new HotelTraderPricingRulesApplier($query, $pricingRules);

        foreach ($supplierResponse as $propertyGroup) {
            $giataId = $propertyGroup['giata_id'] ?? null;
            if (! $giataId || empty($propertyGroup['occupancies'])) {
                continue;
            }
            foreach ($propertyGroup['occupancies'] as $occupancy) {
                $occupancyRefId = $occupancy['occupancyRefId'];
                $guestAges = $occupancy['guestAges'];
                $guestCount = $guestAges === '' ? 0 : count(explode(',', $guestAges));
                $this->occupancies[$giataId][$occupancyRefId] = $guestCount;
            }
        }

        $hotelResponse = [];
        foreach ($supplierResponse as $propertyGroup) {
            yield $this->setHotelResponse($propertyGroup, $query);
        }

        return ['response' => $hotelResponse, 'bookingItems' => $this->bookingItems];
    }

    /**
     * Build hotel-level response from HotelTrader data.
     */
    public function setHotelResponse(array $propertyGroup, array $query): array
    {
        $giataId = Arr::get($propertyGroup, 'giata_id');
        $this->roomCombinations = [];

        $supplierHotelId = Arr::get($propertyGroup, 'propertyId');

        $hotelResponse = HotelResponseFactory::create();
        $hotelResponse->setGiataHotelId($giataId);
        $hotelResponse->setDistanceFromSearchLocation($this->giata[$giataId]['distance'] ?? 0);
        $hotelResponse->setRating(Arr::get($propertyGroup, 'starRating', ''));
        $hotelResponse->setHotelName(Arr::get($propertyGroup, 'hotel_name', ''));
        $hotelResponse->setBoardBasis(Arr::get($propertyGroup, 'board_basis', ''));
        $hotelResponse->setSupplier(SupplierNameEnum::HOTEL_TRADER->value);
        $hotelResponse->setSupplierHotelId($supplierHotelId);
        $hotelResponse->setDestination(Arr::get($propertyGroup, 'city', $this->destinationData));
        $hotelResponse->setMealPlansAvailable(Arr::get($propertyGroup, 'meal_plans_available', ''));
        $hotelResponse->setPayAtHotelAvailable(Arr::get($propertyGroup, 'pay_at_hotel_available', ''));
        $hotelResponse->setPayNowAvailable(Arr::get($propertyGroup, 'pay_now_available', ''));
        $countRefundableRates = $this->fetchCountRefundableRates($propertyGroup);
        $hotelResponse->setNonRefundableRates(Arr::get($countRefundableRates, 'non_refundable_rates', ''));
        $hotelResponse->setRefundableRates(Arr::get($countRefundableRates, 'refundable_rates', ''));
        $roomGroups = [];
        $lowestPrice = 100000;
        foreach ($propertyGroup['rooms'] as $roomGroup) {
            $roomGroupsData = $this->setRoomGroupsResponse($roomGroup, $propertyGroup, $giataId, $supplierHotelId, $query);
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

        $descriptiveContent = DescriptiveContentResolver::getHotelLevel(Arr::get($this->descriptiveContent, $giataId, []), $this->query, $giataId);
        $hotelResponse->setDescriptiveContent($descriptiveContent);

        return $hotelResponse->toArray();
    }

    /**
     * Build room group response from HotelTrader data.
     */
    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup, $giataId, $supplierHotelId, array $query): ?array
    {
        //        dd($roomGroup, $propertyGroup, $giataId, $query);

        $giataId = Arr::get($propertyGroup, 'giata_id');

        $basicHotelData = Arr::get($this->basicHotelData, $propertyGroup['giata_id']);
        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');

        $roomGroupsResponse = RoomGroupsResponseFactory::create();

        $roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
        $roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');
        $roomGroupsResponse->setMealPlan($roomGroup['meal_plan'] ?? '');
        $roomGroupsResponse->setRateDescription($roomGroup['rate_description'] ?? '');
        $roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');

        $this->currency = $roomGroup['rates'][0]['rateInfo']['currency'];
        $roomGroupsResponse->setCurrency($this->currency ?? 'USD');

        $rooms = [];
        $roomsResponse = [];
        $priceRoomData = [];
        foreach ($roomGroup['rates'] as $key => $roomRate) {
            $rateId = Arr::get($roomRate, 'rateplanTag', '');
            // exclude rate codes from the response according to excludeRules
            if (in_array($rateId, $this->exclusionRates)) {
                continue;
            }
            $roomData = $this->setRoomResponse((array) $roomRate, $propertyGroup, $giataId, $supplierHotelId, $query);

            if (empty($roomData)) {
                continue;
            }
            $roomResponse = $roomData['roomResponse'];
            $pricingRulesApplierRoom = $roomData['pricingRulesApplier'];
            $rooms[$key] = $roomResponse;
            $roomsResponse[] = $roomResponse;
            $priceRoomData[$key] = $pricingRulesApplierRoom;
        }
        if (empty($priceRoomData)) {
            return null;
        }

        $roomGroupsResponse->setRooms($roomsResponse);

        $lowestPricedRoom = 1000000;
        $keyLowestPricedRoom = array_key_first($priceRoomData); // set first as default
        $keyLowestPricedBedGroup = array_key_first($priceRoomData[$keyLowestPricedRoom]); // set first as default

        //        dd($keyLowestPricedBedGroup, $keyLowestPricedRoom);

        foreach ($priceRoomData as $key => $priceRoom) {
            if ($priceRoom['total_price'] >= 0 && $priceRoom['total_price'] < $lowestPricedRoom) {
                $lowestPricedRoom = $priceRoom['total_price'];
                $keyLowestPricedRoom = $key;
            }
        }

        /** return lowest priced room data */
        $totalPrice = $priceRoomData[$keyLowestPricedRoom]['total_price'] ?? 0.0;
        $roomGroupsResponse->setTotalPrice($totalPrice);
        $roomGroupsResponse->setTotalTax($priceRoomData[$keyLowestPricedRoom]['total_tax'] ?? 0.0);
        $roomGroupsResponse->setTotalFees($priceRoomData[$keyLowestPricedRoom]['total_fees'] ?? 0.0);
        $roomGroupsResponse->setTotalNet($priceRoomData[$keyLowestPricedRoom]['total_net'] ?? 0.0);
        $roomGroupsResponse->setMarkup($priceRoomData[$keyLowestPricedRoom]['markup'] ?? 0.0);
        $roomGroupsResponse->setMarkup($isCommissionTracking ? 0 : ($priceRoomData[$keyLowestPricedRoom]['markup'] ?? 0.0));
        $roomGroupsResponse->setNonRefundable($rooms[$keyLowestPricedRoom]['non_refundable']);
        $roomGroupsResponse->setRateId(intval($rooms[$keyLowestPricedRoom]['rate_id']) ?? null);
        $roomGroupsResponse->setCancellationPolicies($rooms[$keyLowestPricedRoom]['cancellation_policies']);

        /** @var RoomResponse $roomResponse */
        $roomResponse = app(RoomResponse::class);
        $roomResponseLowestPrice = $roomResponse->fromArray($rooms[$keyLowestPricedRoom]);

        $rating = Arr::get($this->giata, "$giataId.rating", 0);
        $roomGroupsResponse->setDeposits(DepositResolver::getRateLevel($roomResponseLowestPrice, Arr::get($this->depositInformation, $giataId, []), $query, $giataId, $rating, $totalPrice));

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    /**
     * Build room response from HotelTrader data.
     */
    public function setRoomResponse(array $rate, array $propertyGroup, int $giataId, int|string $supplierHotelId, array $query): array
    {
        //                dd($rate, $propertyGroup, $giataId, $supplierHotelId, $query);

        $roomType = Arr::get($rate, 'roomType', '');
        $roomCode = Arr::get($rate, 'roomCode', 0);
        $roomName = Arr::get($rate, 'roomName', '');
        $rateCode = Arr::get($rate, 'rateplanTag', '');
        $roomId = Arr::get($rate, 'occupancyRefId', 1);
        $htIdentifier = Arr::get($rate, 'htIdentifier', '');

        $giataCode = Arr::get($propertyGroup, 'giata_id', 0);

        $unifiedRoomCode = Arr::get($this->unifiedRoomCodes, ContentSourceEnum::HOTEL_TRADER->value, []);
        $unifiedRoomCode = Arr::get($unifiedRoomCode, "$giataCode.$roomCode", '');
        $srRoomId = Arr::get($this->roomIdByUnifiedCode, "$giataCode.$unifiedRoomCode", '');

        $basicHotelData = Arr::get($this->basicHotelData, $giataId);
        $isCommissionTracking = (Arr::get($basicHotelData, 'sale_type') === 'Commission Tracking');

        // enrichment Pricing Rules / Application of Pricing Rules
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['markup'] = 0.0;

        $rateOccupancy = $this->occupancies[$giataId][$rate['occupancyRefId']] ?? 0;
        $rateToApply['Rates'] = $rate['rateInfo'];
        $rateToApply['rateOccupancy'] = $rateOccupancy;

        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply(
                giataId: $giataId,
                roomsPricingArray: $rateToApply,
                roomName: $roomName,
                roomCode: $roomCode,
                roomType: $roomType,
                rateCode: $rateCode,
                srRoomId: $srRoomId,
            );
        } catch (Exception $e) {
            Log::error('HbsiHotelPricingTransformer | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());
        }

        $roomResponse = RoomResponseFactory::create();

        $roomResponse->setGiataRoomCode($srRoomId);
        $roomResponse->setGiataRoomName('');
        $penaltyDate = Arr::get($rate, 'penaltyDate', null);
        if ($roomResponse->getNonRefundable()) {
            $penaltyDate = Carbon::now();
        }
        $roomResponse->setPenaltyDate($penaltyDate);
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($roomName);
        $roomResponse->setSupplierRoomCode($roomId);
        $roomResponse->setUnifiedRoomCode($unifiedRoomCode);
        $roomResponse->setSupplierBedGroups($rate['bed_groups'] ?? 0);
        $roomResponse->setSupplierRoomName($roomName);
        $roomResponse->setRoomType($roomCode);

        $rateDescriptionRaw = Arr::get($rate, 'longDescription', '');
        $rateDescriptionClean = strip_tags($rateDescriptionRaw);
        $rateDescriptionClean = preg_replace('/\s+/', ' ', $rateDescriptionClean);
        $rateDescriptionClean = trim($rateDescriptionClean);
        $roomResponse->setRateDescription($rateDescriptionClean);

        $rateOrdinal = Arr::get($rate, 'rate_ordinal', 0);

        $roomResponse->setRateId($rateOrdinal);
        $roomResponse->setRatePlanCode($rateCode);
        $roomResponse->setRateName($rateCode);
        $roomResponse->setTotalPrice(round($pricingRulesApplier['total_price'], 2));
        $roomResponse->setTotalTax(round($pricingRulesApplier['total_tax'], 2));
        $roomResponse->setTotalFees(round($pricingRulesApplier['total_fees'], 2));
        $roomResponse->setTotalNet(round($pricingRulesApplier['total_net'], 2));

        $roomResponse->setMarkup($pricingRulesApplier['markup']);
        if ($isCommissionTracking) {
            $roomResponse->setMarkup(0);
            $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
            $roomResponse->setCommissionAmount($pricingRulesApplier['markup']);
        }
        $roomResponse->setCommissionableAmount($roomResponse->getTotalPrice() + $roomResponse->getMarkup() - $roomResponse->getTotalTax());
        $roomResponse->setCurrency($this->currency ?? 'USD');

        $roomResponse->setCancellationPolicies(Arr::get($rate, 'cancellationPolicies', []));
        $roomResponse->setNonRefundable(! Arr::get($rate, 'refundable', false));
        $roomResponse->setMealPlans(Arr::get($rate, 'mealplanOptions.mealplanName', ''));
        $roomResponse->setAmenities([]);

        $roomResponse->setBreakdown($this->getBreakdown($rateToApply));

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
            'rate_type' => $this->rate_type,
            'booking_item_data' => json_encode([
                'hotel_id' => Arr::get($propertyGroup, 'giata_id', 0),
                'hotel_supplier_id' => $supplierHotelId,
                'rate_occupancy' => $rateOccupancy,
                'rate_type' => $this->rate_type,
                'rate_ordinal' => $rateOrdinal,
                'room_id' => $htIdentifier,

                'rate_code' => $rateCode,
                'room_code' => $roomCode,

                'htIdentifier' => $htIdentifier,
                'rate' => [
                    'netPrice' => Arr::get($rate, 'rateInfo.netPrice', ''),
                    'tax' => Arr::get($rate, 'rateInfo.tax', ''),
                    'grossPrice' => Arr::get($rate, 'rateInfo.grossPrice', ''),
                    'payAtProperty' => Arr::get($rate, 'rateInfo.payAtProperty', ''),
                    'dailyPrice' => Arr::get($rate, 'rateInfo.dailyPrice', []),
                    'dailyTax' => Arr::get($rate, 'rateInfo.dailyTax', []),
                ],
            ]),
            'booking_pricing_data' => json_encode($booking_pricing_data),
            'created_at' => Carbon::now()->toDateTimeString(),
            'cache_checkpoint' => Arr::get($propertyGroup, 'giata_id', 0).':'.$roomCode.':'.$rateCode,
        ];
        $rating = Arr::get($this->giata, "$giataId.rating", 0);

        $roomResponse->setDescriptiveContent(DescriptiveContentResolver::getRateLevel($roomResponse, Arr::get($this->descriptiveContent, $giataId, []), $query, $giataId));

        return ['roomResponse' => $roomResponse->toArray(), 'pricingRulesApplier' => $pricingRulesApplier];
    }

    private function fetchCountRefundableRates(array $propertyGroup): array
    {
        $refundableRates = [];
        $nonRefundableRates = [];
        foreach ($propertyGroup['rooms'] as $roomGroup) {
            foreach ($roomGroup['rates'] as $rate) {
                if ($rate['refundable']) {
                    $refundableRates[] = $rate['rateplanTag'];
                } else {
                    $nonRefundableRates[] = $rate['rateplanTag'];
                }
            }
        }

        return [
            'refundable_rates' => implode(',', array_unique($refundableRates)),
            'non_refundable_rates' => implode(',', array_unique($nonRefundableRates)),
        ];
    }

    /**
     * Returns a breakdown of nightly rates, taxes, and fees for HotelTrader rates array.
     *
     * @param  array  $roomsPricingArray  The array containing 'Rates' and related info.
     */
    public function getBreakdown(array $roomsPricingArray): array
    {
        $rates = Arr::get($roomsPricingArray, 'Rates', []);
        $nightly = [];
        $fees = [];
        $stay = [];

        // Nightly breakdown
        $dailyPrices = Arr::get($rates, 'dailyPrice', []);
        $dailyTaxes = Arr::get($rates, 'dailyTax', []);
        $currency = Arr::get($rates, 'currency', 'USD');
        $nights = max(count($dailyPrices), count($dailyTaxes));
        for ($i = 0; $i < $nights; $i++) {
            $night = [];
            if (isset($dailyPrices[$i])) {
                $night[] = [
                    'type' => 'base_rate',
                    'value' => (float) $dailyPrices[$i],
                    'currency' => $currency,
                ];
            }
            if (isset($dailyTaxes[$i])) {
                $night[] = [
                    'type' => 'tax',
                    'value' => (float) $dailyTaxes[$i],
                    'currency' => $currency,
                ];
            }
            if ($night) {
                $nightly[] = $night;
            }
        }

        // Fees from aggregateTaxInfo (payAtBooking, payAtProperty)
        $aggregateTaxInfo = Arr::get($rates, 'aggregateTaxInfo', []);
        foreach (['payAtBooking', 'payAtProperty'] as $collector) {
            if (! empty($aggregateTaxInfo[$collector])) {
                foreach ($aggregateTaxInfo[$collector] as $fee) {
                    $fees[] = [
                        'type' => 'fee',
                        'title' => $fee['name'] ?? $collector,
                        'amount' => (float) ($fee['value'] ?? 0),
                        'currency' => $fee['currency'] ?? $currency,
                        'collected_by' => $collector === 'payAtBooking' ? 'booking' : 'property',
                        'description' => $fee['description'] ?? '',
                    ];
                }
            }
        }

        // Fees from taxInfo (detailed per night)
        $taxInfo = Arr::get($rates, 'taxInfo', []);
        foreach (['payAtBooking', 'payAtProperty'] as $collector) {
            if (! empty($taxInfo[$collector])) {
                foreach ($taxInfo[$collector] as $tax) {
                    $stay[] = [
                        'type' => 'tax',
                        'title' => $tax['name'] ?? $collector,
                        'amount' => (float) ($tax['value'] ?? 0),
                        'currency' => $tax['currency'] ?? $currency,
                        'collected_by' => $collector === 'payAtBooking' ? 'booking' : 'property',
                        'description' => $tax['description'] ?? '',
                        'date' => $tax['date'] ?? null,
                    ];
                }
            }
        }

        return [
            'nightly' => $nightly,
            'stay' => $stay,
            'fees' => $fees,
        ];
    }
}
