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

	public function __construct()
	{
		$this->pricingRulesApplier = new ExpediaPricingRulesApplier();
	}

	public function ExpediaToHotelResponse(array $supplierResponse, array $query) : array
	{
		$hotelResponse = [];
		foreach ($supplierResponse as $propertyGroup) {
			$hotelResponse[] = $this->setHotelResponse($propertyGroup, $query);
		}

		return $hotelResponse;
	}

	public function setHotelResponse(array $propertyGroup, $query) : array
	{
		$hotelResponse = new HotelResponse();
		$hotelResponse->setGiataHotelId($propertyGroup['giata_id']);
		$hotelResponse->setSupplier('Expedia');
		$hotelResponse->setSupplierHotelId($propertyGroup['property_id']);
		$hotelResponse->setDestination($query['destination']);
		$hotelResponse->setMealPlansAvailable($propertyGroup['meal_plans_available'] ?? '');
		$hotelResponse->setLowestPricedRoomGroup($propertyGroup['lowest_priced_room_group'] ?? '');
		$hotelResponse->setPayAtHotelAvailable($propertyGroup['pay_at_hotel_available'] ?? '');
		$hotelResponse->setPayNowAvailable($propertyGroup['pay_now_available'] ?? '');
		$hotelResponse->setNonRefundableRates($propertyGroup['non_refundable_rates'] ?? '');
		$hotelResponse->setRefundableRates($propertyGroup['refundable_rates'] ?? '');
		$roomGroups = [];
		foreach ($propertyGroup['rooms'] as $roomGroup) {
			$roomGroups[] = $this->setRoomGroupsResponse((array)$roomGroup, $query, $propertyGroup['property_id']);
		}
		$hotelResponse->setRoomGroups($roomGroups);

		return $hotelResponse->toArray();
	}

	public function setRoomGroupsResponse(array $roomGroup, $query, $giataId) : array
	{
		// dd($roomGroup, $roomGroup['rates'], $roomGroup['rates'][0]->occupancy_pricing);
		$ch = new Channels;
		$channelId = $ch->getTokenId(request()->bearerToken());
		$pricingRulesApplier = [];
		try {
			$pricingRulesApplier = $this->pricingRulesApplier->apply($giataId, $channelId, json_encode($query), json_encode($roomGroup['rates'][0]->occupancy_pricing));
		} catch (\Exception $e) {
			\Log::error('ExpediaHotelDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
		}
		$roomGroupsResponse = new RoomGroupsResponse();
		$roomGroupsResponse->setTotalPrice($pricingRulesApplier['total_price'] ?? 0.0);
		$roomGroupsResponse->setTotalTax($pricingRulesApplier['total_tax'] ?? 0.0);
		$roomGroupsResponse->setTotalFees($pricingRulesApplier['total_fees'] ?? 0.0);
		$roomGroupsResponse->setTotalNet($pricingRulesApplier['total_net'] ?? 0.0);
		$roomGroupsResponse->setCurrency($roomGroup['currency'] ?? '');
		$roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
		$roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');
		$roomGroupsResponse->setNonRefundable($roomGroup['non_refundable'] ?? '');
		$roomGroupsResponse->setMealPlan($roomGroup['meal_plan'] ?? '');
		$roomGroupsResponse->setRateId($roomGroup['rate_id'] ?? '');
		$roomGroupsResponse->setRateDescription($roomGroup['rate_description'] ?? '');
		$roomGroupsResponse->setCancellationPolicies($roomGroup['cancellation_policies'] ?? '');
		$roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');
		$rooms = [];
		foreach ($roomGroup['rates'] as $room) {
			$rooms[] = $this->setRoomResponse((array)$room);
		}
		$roomGroupsResponse->setRooms($rooms);

		return $roomGroupsResponse->toArray();
	}

	public function setRoomResponse(array $room) : array
	{
		$roomResponse = new RoomResponse();
		$roomResponse->setGiataRoomCode($room['giata_room_code'] ?? '');
		$roomResponse->setGiataRoomName($room['giata_room_name'] ?? '');
		$roomResponse->setSupplierRoomName($room['supplier_room_name'] ?? '');
		$roomResponse->setPerDayRateBreakdown($room['per_day_rate_breakdown'] ?? '');

		return $roomResponse->toArray();
	}

}