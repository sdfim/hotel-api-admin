<?php

namespace Modules\API\Suppliers\DTO;

use App\Jobs\SaveBookingItems;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponse;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
use App\Models\Channel;
use App\Models\GiataGeography;
use App\Models\PricingRule;
use App\Models\ApiBookingItem;

class ExpediaPricingDto
{
    /**
     * @var ExpediaPricingRulesApplier
     */
    private ExpediaPricingRulesApplier $pricingRulesApplier;
    /**
     * @var array
     */
    private array $query;
    /**
     * @var string
     */
    private string $search_id;
    /**
     * @var float
     */
    private float $current_time;
    /**
     * @var float
     */
    private float $total_time;

    /**
     * @var array|null
     */
    private array|null $pricingRules;

    /**
     * @var int
     */
    private int $channelId;

    /**
     * @var GiataGeography|null
     */
    private GiataGeography|null $destinationData;

	/**
	 * @var array
	 */
	private array $bookingItems;

    /**
     *
     */
    public function __construct()
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
    public function ExpediaToHotelResponse(array $supplierResponse, array $query, string $search_id): array
    {		
        $this->query = $query;
        $this->search_id = $search_id;
		$this->bookingItems = [];

        $ch = new Channel;
        $token = $ch->getTokenId(request()->bearerToken());
        $this->channelId = Channel::where('token_id', $token)->first()->id;

        $pricingRules = PricingRule::where('supplier_id', 1)
            ->whereIn('property', array_keys($supplierResponse))
            ->where('channel_id', $this->channelId)
            ->where('rating', '>=', (float)$query['rating'])
            ->whereDate('rule_start_date', '<=', $query['checkin'])
            ->whereDate('rule_expiration_date', '>=', $query['checkout'])
            ->get()
            ->toArray();

        $this->pricingRules = [];
        foreach ($pricingRules as $pricingRule) {
            $this->pricingRules[$pricingRule['property']] = $pricingRule;
        }
        foreach ($pricingRules as $pricingRule) {
            $this->pricingRules[$pricingRule['property']] = $pricingRule;
        }

        $this->pricingRulesApplier = new ExpediaPricingRulesApplier($query, $this->pricingRules);

        $this->destinationData = GiataGeography::where('city_id', $this->query['destination'])
            ->select([
                DB::raw("CONCAT(city_name, ', ', locale_name, ', ', country_name) as full_location")
            ])
            ->first();

        $hotelResponse = [];
        foreach ($supplierResponse as $propertyGroup) {
            $hotelResponse[] = $this->setHotelResponse($propertyGroup);
        }
        \Log::info('ExpediaPricingDto | enrichmentPricingRules - ' . $this->total_time . 's');

		SaveBookingItems::dispatch($this->bookingItems);

        return $hotelResponse;
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
        $pricingRulesApplier = [];

        $roomGroupsResponse = new RoomGroupsResponse();

        $roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
        $roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');
        $roomGroupsResponse->setMealPlan($roomGroup['meal_plan'] ?? '');
        $roomGroupsResponse->setRateDescription($roomGroup['rate_description'] ?? '');
        $roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');

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

        # return lowest priced room data
        $roomGroupsResponse->setTotalPrice($priceRoomData[$keyLowestPricedRoom]['total_price'] ?? 0.0);
        $roomGroupsResponse->setTotalTax($priceRoomData[$keyLowestPricedRoom]['total_tax'] ?? 0.0);
        $roomGroupsResponse->setTotalFees($priceRoomData[$keyLowestPricedRoom]['total_fees'] ?? 0.0);
        $roomGroupsResponse->setTotalNet($priceRoomData[$keyLowestPricedRoom]['total_net'] ?? 0.0);
        $roomGroupsResponse->setAffiliateServiceCharge($priceRoomData[$keyLowestPricedRoom]['affiliate_service_charge'] ?? 0.0);

        $roomGroupsResponse->setNonRefundable(!$roomGroup['rates'][$keyLowestPricedRoom]['refundable']);
        $roomGroupsResponse->setRateId(intval($roomGroup['rates'][$keyLowestPricedRoom]['id']) ?? null);
        $roomGroupsResponse->setCancellationPolicies($roomGroup['rates'][$keyLowestPricedRoom]['cancel_penalties'] ?? []);

		$firstRoomCapacityKey = array_key_first($roomGroup['rates'][0]['occupancy_pricing']);
		$currency = $roomGroup['rates'][0]['occupancy_pricing'][$firstRoomCapacityKey]['nightly'][0][0]['currency'];

		$roomGroupsResponse->setCurrency($currency ?? 'USD');

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
        $link = 'api/booking/add-item?';
        $link .= 'search_id=' . $this->search_id;
        $link .= '&supplier=Expedia';
        $link .= '&hotel_id=' . $propertyGroup['giata_id'];
        $link .= '&room_id=' . $roomGroup['id'];
        $link .= '&rate=' . $rate['id'];
        $link .= '&bed_groups=' . array_key_first((array)$rate['bed_groups']);

        # enrichment Pricing Rules / Application of Pricing Rules
        $pricingRulesApplier['total_price'] = 0.0;
        $pricingRulesApplier['total_tax'] = 0.0;
        $pricingRulesApplier['total_fees'] = 0.0;
        $pricingRulesApplier['total_net'] = 0.0;
        $pricingRulesApplier['affiliate_service_charge'] = 0.0;
        $occupancy_pricing = $rate['occupancy_pricing'];
        try {
            $pricingRulesApplier = $this->pricingRulesApplier->apply($giataId, $occupancy_pricing);
        } catch (Exception $e) {
            \Log::error('ExpediaPricingDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
        }

        $roomResponse = new RoomResponse();
        $roomResponse->setGiataRoomCode($rate['giata_room_code'] ?? '');
        $roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
        $roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
        $roomResponse->setSupplierRoomName($roomGroup['room_name'] ?? '');
        $roomResponse->setSupplierRoomCode(intval($roomGroup['id']) ?? null);
        $roomResponse->setSupplierBedGroups(array_key_first((array)$rate['bed_groups']) ?? null);
        $roomResponse->setTotalPrice($pricingRulesApplier['total_price']);
        $roomResponse->setTotalTax($pricingRulesApplier['total_tax']);
        $roomResponse->setTotalFees($pricingRulesApplier['total_fees']);
        $roomResponse->setTotalNet($pricingRulesApplier['total_net']);
        $roomResponse->setAffiliateServiceCharge($pricingRulesApplier['affiliate_service_charge']);
        // $roomResponse->setLinks([
        //     'booking' => [
        //         'method' => 'POST',
        //         'href' => $link
        //     ]
        // ]);
		$bookingItem = Str::uuid()->toString();
		$this->bookingItems[] = [
			'booking_item' => $bookingItem,
			// TODO: get id supplier from DB
			'supplier_id' => '1',
			'search_id' => $this->search_id,
			'booking_item_data' => json_encode([
				'hotel_id' => $propertyGroup['giata_id'],
				'room_id' => $roomGroup['id'],
				'rate' => $rate['id'],
				'bed_groups' => array_key_first((array)$rate['bed_groups']),
			]),
			'created_at' => Carbon::now(),
		];
		$roomResponse->setBookingItem($bookingItem);

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
