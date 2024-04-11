<?php

namespace Modules\API\Suppliers\DTO\Expedia;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\PricingAPI\ResponseModels\HotelResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponseFactory;
use Modules\API\PricingAPI\ResponseModels\RoomResponseFactory;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
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

    public function __construct()
    {
        $this->current_time = microtime(true);
    }

    /**
     * @param array $supplierResponse
     * @param array $query
     * @param string $search_id
     * @param array $pricingRules
     * @return array
     */
    public function ExpediaToHotelResponse(array $supplierResponse, array $query, string $search_id, array $pricingRules): array
    {
        $this->search_id = $search_id;
        $this->bookingItems = [];
        $this->occupancy = $query['occupancy'];

        $this->pricingRulesApplier = new ExpediaPricingRulesApplier($query, $pricingRules);

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

    /**
     * @param array $propertyGroup
     * @return array
     */
    public function setHotelResponse(array $propertyGroup): array
    {
        $this->roomCombinations = [];
        $destination = $this->destinationData;
        $hotelResponse = HotelResponseFactory::create();
        $hotelResponse->setGiataHotelId($propertyGroup['giata_id']);
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
            $roomData = $this->setRoomResponse((array)$room, $roomGroup, $propertyGroup, $giataId);
            $roomResponse = $roomData['roomResponse'];
            $pricingRulesApplierRoom = $roomData['pricingRulesApplier'];
            $rooms[$key] = $roomResponse;
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

        $cancellationPolicies = $rooms[$keyLowestPricedRoom]['cancellation_policies'];

        /** return lowest priced room data */
        $roomGroupsResponse->setTotalPrice($priceRoomData[$keyLowestPricedRoom]['total_price'] ?? 0.0);
        $roomGroupsResponse->setTotalTax($priceRoomData[$keyLowestPricedRoom]['total_tax'] ?? 0.0);
        $roomGroupsResponse->setTotalFees($priceRoomData[$keyLowestPricedRoom]['total_fees'] ?? 0.0);
        $roomGroupsResponse->setTotalNet($priceRoomData[$keyLowestPricedRoom]['total_net'] ?? 0.0);
        $roomGroupsResponse->setAffiliateServiceCharge($priceRoomData[$keyLowestPricedRoom]['affiliate_service_charge'] ?? 0.0);

        $roomGroupsResponse->setNonRefundable(!$roomGroup['rates'][$keyLowestPricedRoom]['refundable']);
        $roomGroupsResponse->setRateId(intval($roomGroup['rates'][$keyLowestPricedRoom]['id']) ?? null);
        $roomGroupsResponse->setCancellationPolicies($cancellationPolicies);

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
        /**  enrichment Pricing Rules / Application of Pricing Rules */
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

        /** https://developers.expediagroup.com/docs/products/rapid/resources/reference/constructing-cancellation-policies */
        $cancelPenalty = $rate['cancel_penalties'];
        $cancellationPolicies = [];
        foreach ($cancelPenalty as $key => $penalty) {
            $data = [];
            if (isset($penalty['start'])) {
                $data['penalty_start_date'] = $penalty['start'];
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

        $roomResponse = RoomResponseFactory::create();
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
        $roomResponse->setCancellationPolicies($cancellationPolicies);
        $roomResponse->setCurrency($this->currency);
        if (isset($rate['bed_groups'][array_key_first((array)$rate['bed_groups'])]['configuration'])) {
            $roomResponse->setBedConfigurations($rate['bed_groups'][array_key_first((array)$rate['bed_groups'])]['configuration']);
        }

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
                'bed_groups' => array_key_first((array)$rate['bed_groups']),
            ]),
            'booking_pricing_data' => json_encode($roomResponse->toArray()),
            'created_at' => Carbon::now(),
        ];

        return ['roomResponse' => $roomResponse->toArray(), 'pricingRulesApplier' => $pricingRulesApplier];
    }

    private function getBreakdown(array $roomsPricingArray): array
    {
        $breakdown = [];
        foreach ($this->occupancy as $room) {
            $roomsKey = isset($room['children_ages']) ? $room['adults'] . '-' . implode(',', $room['children_ages']) : $room['adults'];

            foreach ($roomsPricingArray[$roomsKey]['nightly'] as $night => $expenseItems) {
                foreach ($expenseItems as $expenseItem) {
                    $key = '';
                    if ($expenseItem['type'] === 'base_rate') {
                        $key = 'base_fare';
                        $breakdown[$night][$key]['type'] = 'base_fare';
                        $breakdown[$night][$key]['title'] = 'Room Base Fare';
                    } elseif (str_contains($expenseItem['type'], 'tax')) {
                        $key = 'tax';
                        $breakdown[$night][$key]['type'] = 'tax';
                        $breakdown[$night][$key]['title'] = $expenseItem['type'];
                    } elseif (str_contains($expenseItem['type'], 'fee')) {
                        $key = 'fee';
                        $breakdown[$night][$key]['type'] = 'fee';
                        $breakdown[$night][$key]['title'] = $expenseItem['type'];
                    }
                    if (!isset($breakdown[$night][$key]['amount'])) $breakdown[$night][$key]['amount'] = 0;
                    $breakdown[$night][$key]['amount'] += $expenseItem['value'];
                }
            }
        }

        $breakdownWithoutKeys = [];
        foreach ($breakdown as $item) {
            $breakdownWithoutKeys[] = array_values($item);
        }

        return $breakdownWithoutKeys;
    }
}
