<?php

namespace Modules\API\Suppliers\DTO;

use Modules\API\PricingAPI\ResponseModels\HotelResponse;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
use App\Models\Channels;

class ExpediaHotelDto
{
	private ExpediaPricingRulesApplier $pricingRulesApplier;
	private array $query;
	private string $search_id;

	public function __construct()
	{
		$this->pricingRulesApplier = new ExpediaPricingRulesApplier();
	}

	public function ExpediaToHotelResponse(array $supplierResponse, array $query, string $search_id) : array
	{
		$this->query = $query;
		$this->search_id = $search_id;
		$hotelResponse = [];
		foreach ($supplierResponse as $propertyGroup) {
			$hotelResponse[] = $this->setHotelResponse($propertyGroup);
		}

		return $hotelResponse;
	}

	public function setHotelResponse(array $propertyGroup) : array
	{
		$hotelResponse = new HotelResponse();
		$hotelResponse->setGiataHotelId($propertyGroup['giata_id']);
		$hotelResponse->setSupplier('Expedia');
		$hotelResponse->setSupplierHotelId($propertyGroup['property_id']);
		$hotelResponse->setDestination($this->query['destination']);
		$hotelResponse->setMealPlansAvailable($propertyGroup['meal_plans_available'] ?? '');
		$hotelResponse->setLowestPricedRoomGroup($propertyGroup['lowest_priced_room_group'] ?? '');
		$hotelResponse->setPayAtHotelAvailable($propertyGroup['pay_at_hotel_available'] ?? '');
		$hotelResponse->setPayNowAvailable($propertyGroup['pay_now_available'] ?? '');
		$countRefundableRates = $this->fetchCountRefundableRates($propertyGroup);
		$hotelResponse->setNonRefundableRates($countRefundableRates['non_refundable_rates'] ?? '');
		$hotelResponse->setRefundableRates($countRefundableRates['refundable_rates'] ?? '');
		$roomGroups = [];
		foreach ($propertyGroup['rooms'] as $roomGroup) {
			$roomGroups[] = $this->setRoomGroupsResponse((array)$roomGroup, $propertyGroup);
		}
		$hotelResponse->setRoomGroups($roomGroups);

		return $hotelResponse->toArray();
	}

	private function fetchCountRefundableRates($propertyGroup) : array
	{
		$refundableRates = [];
		$nonRefundableRates = [];
		foreach ($propertyGroup['rooms'] as $roomGroup) {
			foreach ($roomGroup->rates as $rate) {
				if ($rate->refundable) {
					$refundableRates[] = $rate->id;
				} else {
					$nonRefundableRates[] = $rate->id;
				}
				// dd($rate, $refundableRates, $nonRefundableRates);
			}
		}

		return ['refundable_rates' => implode(',', $refundableRates), 'non_refundable_rates' => implode(',', $nonRefundableRates)];
	}

	public function setRoomGroupsResponse(array $roomGroup, $propertyGroup) : array
	{
		$giataId = $propertyGroup['property_id'];
		$ch = new Channels;
		$channelId = $ch->getTokenId(request()->bearerToken());
		$pricingRulesApplier = [];
		// stdclass to array
		$rg = json_decode(json_encode($roomGroup['rates'][0]->occupancy_pricing), true);
		try {
			$pricingRulesApplier = $this->pricingRulesApplier->apply($giataId, $channelId, $this->query, $rg);
		} catch (\Exception $e) {
			\Log::error('ExpediaHotelDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
		}
		$roomGroupsResponse = new RoomGroupsResponse();
		$roomGroupsResponse->setTotalPrice($pricingRulesApplier['total_price'] ?? 0.0);
		$roomGroupsResponse->setTotalTax($pricingRulesApplier['total_tax'] ?? 0.0);
		$roomGroupsResponse->setTotalFees($pricingRulesApplier['total_fees'] ?? 0.0);
		$roomGroupsResponse->setTotalNet($pricingRulesApplier['total_net'] ?? 0.0);
		$roomGroupsResponse->setCurrency($pricingRulesApplier['сurrency'] ?? 'USD');
		$roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
		$roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');
		$roomGroupsResponse->setNonRefundable($roomGroup['rates'][0]->refundable ? false : true);
		$roomGroupsResponse->setMealPlan($roomGroup['meal_plan'] ?? '');
		$roomGroupsResponse->setRateId(intval($roomGroup['rates'][0]->id) ?? null);
		$roomGroupsResponse->setRateDescription($roomGroup['rate_description'] ?? '');
		$roomGroupsResponse->setCancellationPolicies($roomGroup['rates'][0]->cancel_penalties ?? []);
		$roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');
		$rooms = [];
		foreach ($roomGroup['rates'] as $room) {
			$rooms[] = $this->setRoomResponse((array)$room, $roomGroup, $propertyGroup);
		}
		$roomGroupsResponse->setRooms($rooms);

		return $roomGroupsResponse->toArray();
	}

	public function setRoomResponse(array $rate, array $roomGroup, array $propertyGroup) : array
	{
		$link = 'api/booking/add-item?';
		$link .= 'search_id=' . $this->search_id;
		$link .= '&supplier=Expedia';
		$link .= '&hotel_id=' . $propertyGroup['giata_id'];
		$link .= '&room_id=' . $roomGroup['id'];
		$link .= '&rate=' . $rate['id'];
		$link .= '&bed_groups=' . array_key_first((array)$rate['bed_groups']);

		$roomResponse = new RoomResponse();
		$roomResponse->setGiataRoomCode($rate['giata_room_code'] ?? '');
		$roomResponse->setGiataRoomName($rate['giata_room_name'] ?? '');
		$roomResponse->setPerDayRateBreakdown($rate['per_day_rate_breakdown'] ?? '');
		$roomResponse->setSupplierRoomName($roomGroup['room_name'] ?? '');
		$roomResponse->setSupplierRoomCode(intval($roomGroup['id']) ?? null);
		$roomResponse->setSupplierBedGroups(array_key_first((array)$rate['bed_groups']) ?? null);
		$roomResponse->setLinks([
			'booking' => [
				'method' => 'POST',
				'href' => $link
			]
		]);

		return $roomResponse->toArray();
	}

}