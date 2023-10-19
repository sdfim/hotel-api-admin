<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelResponse
{
	private int $giata_hotel_id;
	private string $supplier;
	private int $supplier_hotel_id;
	private string $destination;
	private string$meal_plans_available;
	private string$lowest_priced_room_group;
	private string$pay_at_hotel_available;
	private string $pay_now_available;
	private string $non_refundable_rates;
	private string $refundable_rates;
	private array $room_groups;


	public function setGiataHotelId(int $giata_hotel_id) : void
	{
		$this->giata_hotel_id = $giata_hotel_id;
	}

	public function getGiataHotelId() : int
	{
		return $this->giata_hotel_id;
	}

	public function setSupplier(string $supplier) : void
	{
		$this->supplier = $supplier;
	}

	public function getSupplier() : string
	{
		return $this->supplier;
	}

	public function setSupplierHotelId(int $supplier_hotel_id) : void
	{
		$this->supplier_hotel_id = $supplier_hotel_id;
	}

	public function getSupplierHotelId() : int
	{
		return $this->supplier_hotel_id;
	}

	public function setDestination(string $destination) : void
	{
		$this->destination = $destination;
	}

	public function getDestination() : string
	{
		return $this->destination;
	}

	public function setMealPlansAvailable(string $meal_plans_available) : void
	{
		$this->meal_plans_available = $meal_plans_available;
	}

	public function getMealPlansAvailable() : string
	{
		return $this->meal_plans_available;
	}

	public function setLowestPricedRoomGroup(string $lowest_priced_room_group) : void
	{
		$this->lowest_priced_room_group = $lowest_priced_room_group;
	}

	public function getLowestPricedRoomGroup() : string
	{
		return $this->lowest_priced_room_group;
	}

	public function setPayAtHotelAvailable(string $pay_at_hotel_available) : void
	{
		$this->pay_at_hotel_available = $pay_at_hotel_available;
	}

	public function getPayAtHotelAvailable() : string
	{
		return $this->pay_at_hotel_available;
	}

	public function setPayNowAvailable(string $pay_now_available) : void
	{
		$this->pay_now_available = $pay_now_available;
	}

	public function getPayNowAvailable() : string
	{
		return $this->pay_now_available;
	}

	public function setNonRefundableRates(string $non_refundable_rates) : void
	{
		$this->non_refundable_rates = $non_refundable_rates;
	}

	public function getNonRefundableRates() : string
	{
		return $this->non_refundable_rates;
	}

	public function setRefundableRates(string $refundable_rates) : void
	{
		$this->refundable_rates = $refundable_rates;
	}

	public function getRefundableRates() : string
	{
		return $this->refundable_rates;
	}

	public function setRoomGroups(array $room_groups) : void
	{
		$this->room_groups = $room_groups;
	}

	public function getRoomGroups() : array
	{
		return $this->room_groups;
	}

	public function toArray() : array
	{
		return [
			'giata_hotel_id' => $this->getGiataHotelId(),
			'supplier' => $this->getSupplier(),
			'supplier_hotel_id' => $this->getSupplierHotelId(),
			'destination' => $this->getDestination(),
			'meal_plans_available' => $this->getMealPlansAvailable(),
			'lowest_priced_room_group' => $this->getLowestPricedRoomGroup(),
			'pay_at_hotel_available' => $this->getPayAtHotelAvailable(),
			'pay_now_available' => $this->getPayNowAvailable(),
			'non_refundable_rates' => $this->getNonRefundableRates(),
			'refundable_rates' => $this->getRefundableRates(),
			'room_groups' => $this->getRoomGroups()
		];
	}

	public function toJson() : string
	{
		return json_encode($this->toArray());
	}

	public function __toString() : string
	{
		return $this->toJson();
	}

}