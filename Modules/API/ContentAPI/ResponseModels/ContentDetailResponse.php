<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailResponse
{
    /**
     * @var int
     */
    private int $giata_hotel_code;
    /**
     * @var array
     */
    private array $images;
    /**
     * @var string
     */
    private string $description;
    /**
     * @var string
     */
    private string $hotel_name;
    /**
     * @var string
     */
    private string $distance;
    /**
     * @var string
     */
    private string $latitude;
    /**
     * @var string
     */
    private string $longitude;
    /**
     * @var string
     */
    private string $rating;
    /**
     * @var array
     */
    private array $amenities;
    /**
     * @var string
     */
    private string $giata_destination;
    /**
     * @var string
     */
    private string $user_rating;
    /**
     * @var array
     */
    private array $special_instructions;
    /**
     * @var string
     */
    private string $check_in_time;
    /**
     * @var string
     */
    private string $check_out_time;
    /**
     * @var array
     */
    private array $hotel_fees;
    /**
     * @var array
     */
    private array $policies;
    /**
     * @var array
     */
    private array $descriptions;
    /**
     * @var array
     */
    private array $rooms;
    /**
     * @var string
     */
    private string $address;

    /**
     * @param string $address
     * @return void
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param array $rooms
     * @return void
     */
    public function setRooms(array $rooms): void
    {
        $this->rooms = $rooms;
    }

    /**
     * @return array
     */
    public function getRooms(): array
    {
        return $this->rooms;
    }

    /**
     * @param array $descriptions
     * @return void
     */
    public function setDescriptions(array $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    /**
     * @return array
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    /**
     * @param array $policies
     * @return void
     */
    public function setPolicies(array $policies): void
    {
        $this->policies = $policies;
    }

    /**
     * @return array
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * @param array $hotel_fees
     * @return void
     */
    public function setHotelFees(array $hotel_fees): void
    {
        $this->hotel_fees = $hotel_fees;
    }

    /**
     * @return array
     */
    public function getHotelFees(): array
    {
        return $this->hotel_fees;
    }

    /**
     * @param string $check_out_time
     * @return void
     */
    public function setCheckOutTime(string $check_out_time): void
    {
        $this->check_out_time = $check_out_time;
    }

    /**
     * @return string
     */
    public function getCheckOutTime(): string
    {
        return $this->check_out_time;
    }

    /**
     * @param string $check_in_time
     * @return void
     */
    public function setCheckInTime(string $check_in_time): void
    {
        $this->check_in_time = $check_in_time;
    }

    /**
     * @return string
     */
    public function getCheckInTime(): string
    {
        return $this->check_in_time;
    }

    /**
     * @param array $special_instructions
     * @return void
     */
    public function setSpecialInstructions(array $special_instructions): void
    {
        $this->special_instructions = $special_instructions;
    }

    /**
     * @return array
     */
    public function getSpecialInstructions(): array
    {
        return $this->special_instructions;
    }

    /**
     * @param string $user_rating
     * @return void
     */
    public function setUserRating(string $user_rating): void
    {
        $this->user_rating = $user_rating;
    }

    /**
     * @return string
     */
    public function getUserRating(): string
    {
        return $this->user_rating;
    }

    /**
     * @param string $giata_destination
     * @return void
     */
    public function setGiataDestination(string $giata_destination): void
    {
        $this->giata_destination = $giata_destination;
    }

    /**
     * @return string
     */
    public function getGiataDestination(): string
    {
        return $this->giata_destination;
    }

    /**
     * @param array $amenities
     * @return void
     */
    public function setAmenities(array $amenities): void
    {
        $this->amenities = $amenities;
    }

    /**
     * @return array
     */
    public function getAmenities(): array
    {
        return $this->amenities;
    }

    /**
     * @param string $rating
     * @return void
     */
    public function setRating(string $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * @return string
     */
    public function getRating(): string
    {
        return $this->rating;
    }

    /**
     * @param string $longitude
     * @return void
     */
    public function setLongitude(string $longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @param string $latitude
     * @return void
     */
    public function setLatitude(string $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @param string $distance
     * @return void
     */
    public function setDistance(string $distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return string
     */
    public function getDistance(): string
    {
        return $this->distance;
    }

    /**
     * @param string $hotel_name
     * @return void
     */
    public function setHotelName(string $hotel_name): void
    {
        $this->hotel_name = $hotel_name;
    }

    /**
     * @return string
     */
    public function getHotelName(): string
    {
        return $this->hotel_name;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param array $images
     * @return void
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param int $giata_hotel_code
     * @return void
     */
    public function setGiataHotelCode(int $giata_hotel_code): void
    {
        $this->giata_hotel_code = $giata_hotel_code;
    }

    /**
     * @return int
     */
    public function getGiataHotelCode(): int
    {
        return $this->giata_hotel_code;
    }

    /**
     * @return array
     */
    public function toArray(): array
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
