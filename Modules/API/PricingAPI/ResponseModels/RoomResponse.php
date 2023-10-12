<?php

namespace Modules\API\PricingAPI\ResponseModels;

class RoomResponse
{
	private string $giata_room_code;
	private string $giata_room_name;
	private string $supplier_room_name;
	private int $supplier_room_code;
	private string $per_day_rate_breakdown;
	private int $supplier_bed_groups;

	public function setSupplierBedGroups(int $supplier_bed_groups) : void
	{
		$this->supplier_bed_groups = $supplier_bed_groups;
	}

	public function getSupplierBedGroups() : int
	{
		return $this->supplier_bed_groups;
	}

	public function setSupplierRoomCode(int $supplier_room_code) : void
	{
		$this->supplier_room_code = $supplier_room_code;
	}

	public function getSupplierRoomCode() : int
	{
		return $this->supplier_room_code;
	}

	public function setGiataRoomCode(string $giata_room_code) : void
	{
		$this->giata_room_code = $giata_room_code;
	}

	public function getGiataRoomCode() : string
	{
		return $this->giata_room_code;
	}

	public function setGiataRoomName(string $giata_room_name) : void
	{
		$this->giata_room_name = $giata_room_name;
	}

	public function getGiataRoomName() : string
	{
		return $this->giata_room_name;
	}

	public function setSupplierRoomName(string $supplier_room_name) : void
	{
		$this->supplier_room_name = $supplier_room_name;
	}

	public function getSupplierRoomName() : string
	{
		return $this->supplier_room_name;
	}

	public function setPerDayRateBreakdown(string $per_day_rate_breakdown) : void
	{
		$this->per_day_rate_breakdown = $per_day_rate_breakdown;
	}

	public function getPerDayRateBreakdown() : string
	{
		return $this->per_day_rate_breakdown;
	}

	public function toArray() : array
	{
		return [
			'giata_room_code' => $this->getGiataRoomCode(),
			'giata_room_name' => $this->getGiataRoomName(),
			'supplier_room_name' => $this->getSupplierRoomName(),
			'per_day_rate_breakdown' => $this->getPerDayRateBreakdown(),
			'supplier_room_code' => $this->getSupplierRoomCode(),
			'supplier_bed_groups' => $this->getSupplierBedGroups()
		];
	}
}