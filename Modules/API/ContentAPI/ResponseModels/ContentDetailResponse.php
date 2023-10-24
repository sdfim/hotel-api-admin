<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailResponse
{
	private int $giata_hotel_code;
	private array $images;
	private string $description;
	private string $hotel_name;
	private string $distance;
	private string $latitude;
	private string $longitude;
	private string $rating;
	private array $amenities;
	private string $giata_destination;
	private string $user_rating;
	private array $special_instructions;
	private string $check_in_time;
	private string $check_out_time;
	private array $hotel_fees;
	private array $policies;
	private array $descriptions;
	private array $rooms;
	private string $address;

	public function setAddress(string $address) : void
	{
		$this->address = $address;
	}

	public function getAddress() : string
	{
		return $this->address;
	}

	public function setRooms(array $rooms) : void
	{
		$this->rooms = $rooms;
	}

	public function getRooms() : array
	{
		return $this->rooms;
	}

	public function setDescriptions(array $descriptions) : void
	{
		$this->descriptions = $descriptions;
	}

	public function getDescriptions() : array
	{
		return $this->descriptions;
	}

	public function setPolicies(array $policies) : void
	{
		$this->policies = $policies;
	}

	public function getPolicies() : array
	{
		return $this->policies;
	}

	public function setHotelFees(array $hotel_fees) : void
	{
		$this->hotel_fees = $hotel_fees;
	}

	public function getHotelFees() : array
	{
		return $this->hotel_fees;
	}

	public function setCheckOutTime(string $check_out_time) : void
	{
		$this->check_out_time = $check_out_time;
	}

	public function getCheckOutTime() : string
	{
		return $this->check_out_time;
	}

	public function setCheckInTime(string $check_in_time) : void
	{
		$this->check_in_time = $check_in_time;
	}

	public function getCheckInTime() : string
	{
		return $this->check_in_time;
	}

	public function setSpecialInstructions(array $special_instructions) : void
	{
		$this->special_instructions = $special_instructions;
	}

	public function getSpecialInstructions() : array
	{
		return $this->special_instructions;
	}

	public function setUserRating(string $user_rating) : void
	{
		$this->user_rating = $user_rating;
	}

	public function getUserRating() : string
	{
		return $this->user_rating;
	}

	public function setGiataDestination(string $giata_destination) : void
	{
		$this->giata_destination = $giata_destination;
	}

	public function getGiataDestination() : string
	{
		return $this->giata_destination;
	}

	public function setAmenities(array $amenities) : void
	{
		$this->amenities = $amenities;
	}

	public function getAmenities() : array
	{
		return $this->amenities;
	}

	public function setRating(string $rating) : void
	{
		$this->rating = $rating;
	}

	public function getRating() : string
	{
		return $this->rating;
	}

	public function setLongitude(string $longitude) : void
	{
		$this->longitude = $longitude;
	}

	public function getLongitude() : string
	{
		return $this->longitude;
	}

	public function setLatitude(string $latitude) : void
	{
		$this->latitude = $latitude;
	}

	public function getLatitude() : string
	{
		return $this->latitude;
	}

	public function setDistance(string $distance) : void
	{
		$this->distance = $distance;
	}

	public function getDistance() : string
	{
		return $this->distance;
	}

	public function setHotelName(string $hotel_name) : void
	{
		$this->hotel_name = $hotel_name;
	}

	public function getHotelName() : string
	{
		return $this->hotel_name;
	}

	public function setDescription(string $description) : void
	{
		$this->description = $description;
	}

	public function getDescription() : string
	{
		return $this->description;
	}

	public function setImages(array $images) : void
	{
		$this->images = $images;
	}

	public function getImages() : array
	{
		return $this->images;
	}

	public function setGiataHotelCode(int $giata_hotel_code) : void
	{
		$this->giata_hotel_code = $giata_hotel_code;
	}

	public function getGiataHotelCode() : int
	{
		return $this->giata_hotel_code;
	}

	public function toArray() : array
	{
		return [
			'giata_hotel_code' => $this->getGiataHotelCode(),
			'images' => $this->getImages(),
			'description' => $this->getDescription(),
			'hotel_name' => $this->getHotelName(),
			'distance' => $this->getDistance(),
			'latitude' => $this->getLatitude(),
			'longitude' => $this->getLongitude(),
			'rating' => $this->getRating(),
			'amenities' => $this->getAmenities(),
			'giata_destination' => $this->getGiataDestination(),
			'user_rating' => $this->getUserRating(),
			'special_instructions' => $this->getSpecialInstructions(),
			'check_in_time' => $this->getCheckInTime(),
			'check_out_time' => $this->getCheckOutTime(),
			'hotel_fees' => $this->getHotelFees(),
			'policies' => $this->getPolicies(),
			'descriptions' => $this->getDescriptions(),
			'address' => $this->getAddress(),
			'rooms' => $this->getRooms(),
		];
	}
}