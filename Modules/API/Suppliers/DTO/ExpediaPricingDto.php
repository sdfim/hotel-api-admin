<?php

namespace Modules\API\Suppliers\DTO;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\API\PricingAPI\ResponseModels\HotelResponse;
use Modules\API\PricingAPI\ResponseModels\RoomGroupsResponse;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
use App\Models\Channel;
use App\Models\GiataGeography;

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
	 * @var float|int
	 */
	private float|int $lowest_priced_room_group;
	/**
	 * @var float
	 */
	private float $current_time;
	/**
	 * @var float
	 */
	private float $total_time;

	/**
	 *
	 */
	public function __construct()
	{
		$this->pricingRulesApplier = new ExpediaPricingRulesApplier();
		$this->lowest_priced_room_group = 100000;
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
		$hotelResponse = [];
		foreach ($supplierResponse as $propertyGroup) {
			$hotelResponse[] = $this->setHotelResponse($propertyGroup);
		}
		\Log::info('ExpediaPricingDto | enrichmentPricingRules - ' . $this->total_time . 's');

		return $hotelResponse;
	}

	/**
	 * @param array $propertyGroup
	 * @return array
	 */
	public function setHotelResponse(array $propertyGroup): array
	{
		$destinationData = GiataGeography::where('city_id', $this->query['destination'])
			->select([
				DB::raw("CONCAT(city_name, ', ', locale_name, ', ', country_name) as full_location")
			])
			->first();
		$destination = $destinationData->full_location ?? '';
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
		foreach ($propertyGroup['rooms'] as $roomGroup) {
			$roomGroups[] = $this->setRoomGroupsResponse($roomGroup, $propertyGroup);
		}
		$hotelResponse->setRoomGroups($roomGroups);

		$hotelResponse->setLowestPricedRoomGroup($this->lowest_priced_room_group != 100000 ? $this->lowest_priced_room_group : '');

		return $hotelResponse->toArray();
	}

	/**
	 * @param $propertyGroup
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
		$giataId = $propertyGroup['property_id'];
		$ch = new Channel;
		$channelId = $ch->getTokenId(request()->bearerToken());
		$pricingRulesApplier = [];
		// stdclass to array
		// TODO: check rates - is array in payload Expedia
		// dd($roomGroup, $roomGroup['rates']);
		$rg = $roomGroup['rates'][0]['occupancy_pricing'];
		try {
			$this->executionTime();
			# enrichment Pricing Rules / Application of Pricing Rules
			$pricingRulesApplier = $this->pricingRulesApplier->apply($giataId, $channelId, $this->query, $rg);

			$this->total_time += $this->executionTime();

			if ($pricingRulesApplier['total_price'] > 0 && $pricingRulesApplier['total_price'] < $this->lowest_priced_room_group) {
				$this->lowest_priced_room_group = $pricingRulesApplier['total_price'];
			}
		} catch (Exception $e) {
			\Log::error('ExpediaPricingDto | setRoomGroupsResponse ', ['error' => $e->getMessage()]);
		}
		$roomGroupsResponse = new RoomGroupsResponse();
		$roomGroupsResponse->setTotalPrice($pricingRulesApplier['total_price'] ?? 0.0);
		$roomGroupsResponse->setTotalTax($pricingRulesApplier['total_tax'] ?? 0.0);
		$roomGroupsResponse->setTotalFees($pricingRulesApplier['total_fees'] ?? 0.0);
		$roomGroupsResponse->setTotalNet($pricingRulesApplier['total_net'] ?? 0.0);
		$roomGroupsResponse->setAffiliateServiceCharge($pricingRulesApplier['affiliate_service_charge'] ?? 0.0);
		$roomGroupsResponse->setCurrency($pricingRulesApplier['currency'] ?? 'USD');
		$roomGroupsResponse->setPayNow($roomGroup['pay_now'] ?? '');
		$roomGroupsResponse->setPayAtHotel($roomGroup['pay_at_hotel'] ?? '');
		$roomGroupsResponse->setNonRefundable(!$roomGroup['rates'][0]['refundable']);
		$roomGroupsResponse->setMealPlan($roomGroup['meal_plan'] ?? '');
		$roomGroupsResponse->setRateId(intval($roomGroup['rates'][0]['id']) ?? null);
		$roomGroupsResponse->setRateDescription($roomGroup['rate_description'] ?? '');
		$roomGroupsResponse->setCancellationPolicies($roomGroup['rates'][0]['cancel_penalties'] ?? []);
		$roomGroupsResponse->setOpaque($roomGroup['opaque'] ?? '');
		$rooms = [];
		foreach ($roomGroup['rates'] as $room) {
			$rooms[] = $this->setRoomResponse((array)$room, $roomGroup, $propertyGroup);
		}
		$roomGroupsResponse->setRooms($rooms);

		return $roomGroupsResponse->toArray();
	}

	/**
	 * @param array $rate
	 * @param array $roomGroup
	 * @param array $propertyGroup
	 * @return array
	 */
	public function setRoomResponse(array $rate, array $roomGroup, array $propertyGroup): array
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
