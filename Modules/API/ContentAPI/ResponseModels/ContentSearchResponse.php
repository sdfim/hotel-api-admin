<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentSearchResponse
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
    private array $description;
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
    private array $important_information;

    /**
     * @return array
     */
    public function getImportantInformation(): array
    {
        return $this->important_information;
    }

    /**
     * @param array $important_information
     * @return void
     */
    public function setImportantInformation(array $important_information): void
    {
        $this->important_information = $important_information;
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
     * @param array $description
     * @return void
     */
    public function setDescription(array $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getDescription(): array
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
            'amenities' => array_values($this->getAmenities()),
            'giata_destination' => $this->getGiataDestination(),
            'user_rating' => $this->getUserRating(),
            'important_information' => $this->getImportantInformation(),
        ];
    }
}
