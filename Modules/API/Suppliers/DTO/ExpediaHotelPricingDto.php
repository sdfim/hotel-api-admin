<?php

namespace Modules\API\Suppliers\DTO;

use App\Models\GiataGeography;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponse;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
use Modules\API\Tools\PricingRulesTools;

class ExpediaHotelPricingDto
{
    /**
     * @var ExpediaPricingRulesApplier
     */
    private ExpediaPricingRulesApplier $pricingRulesApplier;

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
     * @var GiataGeography|null
     */
    private ?GiataGeography $destinationData;

    /**
     * @var array
     */
    private array $bookingItems;

    /**
     * @var int
     */
    private int $supplierId = 1;

    /**
     * @var PricingRulesTools
     */
    private PricingRulesTools $pricingRulesService;

    /**
     *
     */
    public function __construct()
    {
        $this->current_time = microtime(true);
        $this->total_time = 0.0;
        $this->pricingRulesService = new PricingRulesTools();
    }

    /**
     * @param array $supplierResponse
     * @param array $query
     * @param string $search_id
     * @return array
     */
    public function ExpediaToHotelResponse(array $supplierResponse, array $query, string $search_id): array
    {
        $this->search_id = $search_id;
        $this->bookingItems = [];

        $pricingRules = $this->pricingRulesService->rules($query);

        foreach ($pricingRules as $pricingRule) {
            // TODO: remove isset
            if (isset($pricingRule['property'])) $pricingRules[$pricingRule['property']] = $pricingRule;
        }

        $this->pricingRulesApplier = new ExpediaPricingRulesApplier($query, $pricingRules);

        $this->destinationData = GiataGeography::where('city_id', $query['destination'])
            ->select([
                DB::raw("CONCAT(city_name, ', ', locale_name, ', ', country_name) as full_location"),
            ])
            ->first();

        $hotelResponse = [];
        foreach ($supplierResponse as $propertyGroup) {
            $hotelResponse[] = $this->setHotelResponse($propertyGroup);
        }
        Log::info('ExpediaHotelPricingDto | enrichmentPricingRules - ' . $this->total_time . 's');

        // TODO: uncomment this line after add Redis
        // SaveBookingItems::dispatch($this->bookingItems);

        return ['response' => $hotelResponse, 'bookingItems' => $this->bookingItems];
    }

    /**
     * @param array $propertyGroup
     * @return array
     */
    public function setHotelResponse(array $propertyGroup): array
    {

        $destination = $this->destinationData->full_location ?? '';
        $hotelResponse = new HotelResponse();
        $hotelResponse->setGiataHotelId($propertyGroup['giata_id']);
        $hotelResponse->setHotelName($propertyGroup['hotel_name'] ?? '');
        $hotelResponse->setBoardBasis(($propertyGroup['board_basis'] ?? ''));
        $hotelResponse->setSupplier('Expedia');
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
    public function setRoomGroupsResponse(array $roomGroup, $propertyGroup): array
    {
        $giataId = $propertyGroup['giata_id'];

        $roomGroupsResponse = new RoomGroupsResponse();

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
            $roomData = $this->setRoomResponse((array)$room, $roomGroup, $propertyGroup, $giataId);
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

        $roomGroupsResponse->setNonRefundable(!$roomGroup['rates'][$keyLowestPricedRoom]['refundable']);
        $roomGroupsResponse->setRateId(intval($roomGroup['rates'][$keyLowestPricedRoom]['id']) ?? null);
        $roomGroupsResponse->setCancellationPolicies($roomGroup['rates'][$keyLowestPricedRoom]['cancel_penalties'] ?? []);

        return ['roomGroupsResponse' => $roomGroupsResponse->toArray(), 'lowestPricedRoom' => $lowestPricedRoom];
    }

    /**
     * @param array $rate
     * @param array $roomGroup
     * @param array $propertyGroup
     * @param int $giataId
     * @return array
     */
    public function setRoomResponse(array $rate, array $roomGroup, array $propertyGroup, int $giataId): array
    {
        // enrichment Pricing Rules / Application of Pricing Rules
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['affiliate_service_charge'] = 0.0;
        $occupancy_pricing = $rate['occupancy_pricing'];
        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply($giataId, $occupancy_pricing);
        } catch (Exception $e) {
            Log::error('ExpediaHotelPricingDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
        }

        if ($pricingRulesApplier['total_price'] == 0.0) {
            Log::error('ExpediaHotelPricingDto | setRoomGroupsResponse ', ['error' => 'total_price == 0.0']);
        }

        $roomResponse = new RoomResponse();
        $roomResponse->setGiataRoomCode($rate['giata_room_code'] ?? '');
        $roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($roomGroup['room_name'] ?? '');
        $roomResponse->setSupplierRoomCode(intval($roomGroup['id']) ?? null);
        $roomResponse->setSupplierBedGroups(array_key_first((array)$rate['bed_groups']) ?? null);
        $roomResponse->setRoomType('');
        $roomResponse->setRateDescription($rate['description'] ?? '');
        $roomResponse->setRateId($rate['id'] ?? '');
        $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
        $roomResponse->setTotalTax($pricingRulesApplier['total_tax']);
        $roomResponse->setTotalFees($pricingRulesApplier['total_fees']);
        $roomResponse->setTotalNet($pricingRulesApplier['total_net']);
        $roomResponse->setAffiliateServiceCharge($pricingRulesApplier['affiliate_service_charge']);
        $roomResponse->setCurrency($this->currency);

        $bookingItem = Str::uuid()->toString();
        $roomResponse->setBookingItem($bookingItem);

        $this->bookingItems[] = [
            'booking_item' => $bookingItem,
            'supplier_id' => $this->supplierId,
            'search_id' => $this->search_id,
            'booking_item_data' => json_encode([
                'hotel_id' => $propertyGroup['giata_id'],
                'room_id' => $roomGroup['id'],
                'rate' => $rate['id'],
                'bed_groups' => array_key_first((array)$rate['bed_groups']),
            ]),
            'booking_pricing_data' => json_encode($roomResponse->toArray()),
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
