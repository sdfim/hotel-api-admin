<?php

namespace Modules\API\Suppliers\DTO\HBSI;

use App\Models\GiataGeography;
use App\Models\Channel;
use App\Models\Supplier;
use App\Repositories\ChannelRenository;
use App\Repositories\GiataGeographyRepository;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponse;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\API\PricingRules\HBSI\HbsiPricingRulesApplier;
use Modules\API\Tools\PricingRulesTools;

class HbsiHotelPricingDto
{
    private const SINGLE_TYPE_ITEM = 'single';
    private const COMPLETE_TYPE_ITEM = 'complete';

    private string $rate_type;

    /**
     * @var GiataGeography
     */
    private GiataGeography $destinationData;

    /**
     * @var HbsiPricingRulesApplier
     */
    private HbsiPricingRulesApplier $pricingRulesApplier;

    /**
     * @var string
     */
    private string $search_id;

    /**
     * @var string
     */
    private string $currency;

    /**
     * @var float
     */
    private float $current_time;

    /**
     * @var float
     */
    private float $total_time;

    /**
     * @param array $bookingItems
     * @param int $supplierId
     * @param PricingRulesTools $pricingRulesService
     * @param GiataGeographyRepository $geographyRepo
     */
    public function __construct(
        private array $bookingItems = [],
        private readonly int $supplierId = 2,
        private readonly PricingRulesTools $pricingRulesService = new PricingRulesTools(),
        private readonly GiataGeographyRepository $geographyRepo = new GiataGeographyRepository()
    )
    {
        $this->current_time = microtime(true);
        $this->total_time = 0.0;
    }

    /**
     * @param array $supplierResponse
     * @param array $query
     * @param string $search_id
     * @return array
     */
    public function HbsiToHotelResponse(array $supplierResponse, array $query, string $search_id): array
    {
        $this->search_id = $search_id;
        $this->rate_type = count($query['occupancy']) === 1 ? self::COMPLETE_TYPE_ITEM : self::SINGLE_TYPE_ITEM;

        $token = ChannelRenository::getTokenId(request()->bearerToken());

        $channelId = Channel::where('token_id', $token)->first()->id;

        $supplierId = Supplier::where('name', 'HBSI')->first()->id;

        $pricingRules = $this->pricingRulesService->rules($query, $channelId, $supplierId);
        $pricingRules = array_column($pricingRules, null, 'property');

        $this->pricingRulesApplier = new HbsiPricingRulesApplier($query, $pricingRules);

        $this->destinationData = $this->geographyRepo->getFullLocation($query['destination']);

        $hotelResponse = [];
        foreach ($supplierResponse as $key => $propertyGroup) {
            $hotelResponse[] = $this->setHotelResponse($propertyGroup, $key);
        }
        Log::info('HbsiToHotelResponse | enrichmentPricingRules - ' . $this->total_time . 's');

        // TODO: uncomment this line after add Redis
        // SaveBookingItems::dispatch($this->bookingItems);

        return ['response' => $hotelResponse, 'bookingItems' => $this->bookingItems];
    }

    /**
     * @param array $propertyGroup
     * @param int|string $key
     * @return array
     */
    public function setHotelResponse(array $propertyGroup, int|string $key): array
    {
        $hotelResponse = new HotelResponse();
        $hotelResponse->setGiataHotelId($propertyGroup['giata_id'] ?? 0);
        $hotelResponse->setHotelName($propertyGroup['hotel_name'] ?? '');
        $hotelResponse->setBoardBasis(($propertyGroup['board_basis'] ?? ''));
        $hotelResponse->setSupplier('HBSI');
        $hotelResponse->setSupplierHotelId($key);
        $hotelResponse->setDestination($this->destinationData->full_location ?? '');
        $hotelResponse->setMealPlansAvailable($propertyGroup['meal_plans_available'] ?? '');
        $hotelResponse->setPayAtHotelAvailable($propertyGroup['pay_at_hotel_available'] ?? '');
        $hotelResponse->setPayNowAvailable($propertyGroup['pay_now_available'] ?? '');
//        $countRefundableRates = $this->fetchCountRefundableRates($propertyGroup);
        $hotelResponse->setNonRefundableRates($countRefundableRates['non_refundable_rates'] ?? '');
        $hotelResponse->setRefundableRates($countRefundableRates['refundable_rates'] ?? '');
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

        $hotelResponse->setRoomGroups($roomGroups);
        $hotelResponse->setLowestPricedRoomGroup($lowestPrice != 100000 ? $lowestPrice : '');

        return $hotelResponse->toArray();
    }

    /**
     * @param array $propertyGroup
     * @return array
     */
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

    /**
     * @param array $roomGroup
     * @param $propertyGroup
     * @return array
     */
    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup, int|string $supplierHotelId): array
    {
        $giataId = $propertyGroup['giata_id'] ?? 0;

        $roomGroupsResponse = new RoomGroupsResponse();
        $roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
        $roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');
        $roomGroupsResponse->setMealPlan($roomGroup['MealPlanCodes'] ?? '');
        $roomGroupsResponse->setRateDescription($roomGroup['RoomRateDescription'] ?? '');
        $roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');

        $this->currency = $roomGroup['rates']['RoomRates']['RoomRate']['Rates']['Rate'][0]['Base']['@attributes']['CurrencyCode'] ?? 'USD';
        $roomGroupsResponse->setCurrency($this->currency ?? 'USD');

        $rooms = [];
        $priceRoomData = [];
        foreach ($roomGroup['rates'] as $key => $room) {
            $roomData = $this->setRoomResponse((array)$room, $roomGroup, $propertyGroup, $giataId, $supplierHotelId);
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
        $roomGroupsResponse->setAffiliateServiceCharge($priceRoomData[$keyLowestPricedRoom]['affiliate_service_charge'] ?? 0.0);

        $roomGroupsResponse->setNonRefundable($roomGroup['CancelPenalties']['CancelPenalty']['@attributes']['NonRefundable'] ?? false);
        $roomGroupsResponse->setRateId($roomGroup['rate_ordinal'] ?? 0);
        $roomGroupsResponse->setCancellationPolicies([
            'Deadline' => $roomGroup['CancelPenalties']['CancelPenalty']['Deadline']['@attributes']['AbsoluteDeadline'] ?? '',
            'AmountPercent' => $roomGroup['CancelPenalties']['CancelPenalty']['AmountPercent']['@attributes']['Percent'] ?? '',
            ]);

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    /**
     * @param array $rate
     * @param array $roomGroup
     * @param array $propertyGroup
     * @param int $giataId
     * @param int|string $supplierHotelId
     * @return array
     */
    public function setRoomResponse(array $rate, array $roomGroup, array $propertyGroup, int $giataId, int|string $supplierHotelId): array
    {
        // enrichment Pricing Rules / Application of Pricing Rules
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['affiliate_service_charge'] = 0.0;
        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply($giataId, $rate);
        } catch (Exception $e) {
            Log::error('HbsiHotelPricingDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
        }

        $counts = [];
        foreach ($rate['GuestCounts']['GuestCount'] as $guestCount) {
            if (isset($guestCount['AgeQualifyingCode'])) $counts[$guestCount['AgeQualifyingCode']] = $guestCount['Count'];
            else $counts[$guestCount['@attributes']['AgeQualifyingCode']] = $guestCount['@attributes']['Count'];
        }
        $rateOccupancy = $counts['10'] . '-' . ($counts['8'] ?? 0) . '-' . ($counts['7'] ?? 0);
        $rateOrdinal = $rate['rate_ordinal'] ?? 0;

        $roomType = $rate['RoomTypes']['RoomType']['@attributes']['RoomTypeCode'] ?? '';

        $roomResponse = new RoomResponse();
        $roomResponse->setGiataRoomCode($rate['giata_room_code'] ?? '');
        $roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($rate['RoomTypes']['RoomType']['RoomDescription']['@attributes']['Name'] ?? '');
//        $roomResponse->setSupplierRoomCode($rate['RoomTypes']['RoomType']['@attributes']['NumberOfUnits'] ?? 0);
        $roomResponse->setSupplierRoomCode($rateOccupancy);
        $roomResponse->setSupplierBedGroups($rate['bed_groups'] ?? 0);
        $roomResponse->setRoomType($roomType);
        $roomResponse->setRateDescription($rate['RoomRates']['RoomRate']['RoomRateDescription']['Text'] ?? '');
        $roomResponse->setRateId($rateOrdinal );
        $roomResponse->setRatePlanCode($rate['RatePlans']['RatePlan']['@attributes']['RatePlanCode'] ?? '');
        $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
        $roomResponse->setTotalTax($pricingRulesApplier['total_tax']);
        $roomResponse->setTotalFees($pricingRulesApplier['total_fees']);
        $roomResponse->setTotalNet($pricingRulesApplier['total_net']);
        $roomResponse->setAffiliateServiceCharge($pricingRulesApplier['affiliate_service_charge']);
        $roomResponse->setCurrency($this->currency ?? 'USD');

        $bookingItem = Str::uuid()->toString();
        $roomResponse->setBookingItem($bookingItem);

        $booking_pricing_data = $roomResponse->toArray();
        $booking_pricing_data['rate_description'] = mb_substr($booking_pricing_data['rate_description'], 0, 200, 'UTF-8');;

        $this->bookingItems[] = [
            'booking_item' => $bookingItem,
            'supplier_id' => $this->supplierId,
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
        ];

        return ['roomResponse' => $roomResponse->toArray(), 'pricingRulesApplier' => $pricingRulesApplier];
    }

    /**
     * @return string|float
     */
    private function executionTime(): string|float
    {
        $execution_time = round((microtime(true) - $this->current_time), 3);
        $this->current_time = microtime(true);

        return $execution_time;
    }
}
