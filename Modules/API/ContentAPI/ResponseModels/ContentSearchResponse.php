<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentSearchResponse
{
    private int $giata_hotel_code;

    private array $images;

    private array $description;

    private string $hotel_name;

    private string $latitude;

    private string $longitude;

    private string $rating;

    private array $amenities;

    private string $giata_destination;

    private string $user_rating;

    private array $important_information;

    private int $weight;

    private array $deposit_information;

    private array $cancellation_policies;

    private array $drivers;

    private array $ultimate_amenities;

    private array $nearest_airports;

    private string $currency;

    private string $number_rooms;

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setNumberRooms(string $number_rooms): void
    {
        $this->number_rooms = $number_rooms;
    }

    public function getNumberRooms(): string
    {
        return $this->number_rooms;
    }

    public function setNearestAirports(array $nearest_airports): void
    {
        $this->nearest_airports = $nearest_airports;
    }

    public function getNearestAirports(): array
    {
        return $this->nearest_airports;
    }

    public function setUltimateAmenities(array $ultimate_amenities): void
    {
        $this->ultimate_amenities = $ultimate_amenities;
    }

    public function getUltimateAmenities(): array
    {
        return $this->ultimate_amenities;
    }

    public function setDrivers(array $drivers): void
    {
        $this->drivers = $drivers;
    }

    public function getDrivers(): array
    {
        return $this->drivers;
    }

    public function setCancellationPolicies(array $cancellation_policies): void
    {
        $this->cancellation_policies = $cancellation_policies;
    }

    public function getCancellationPolicies(): array
    {
        return $this->cancellation_policies;
    }

    public function setDepositInformation(array $deposit_information): void
    {
        $this->deposit_information = $deposit_information;
    }

    public function getDepositInformation(): array
    {
        return $this->deposit_information;
    }

    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getImportantInformation(): array
    {
        return $this->important_information;
    }

    public function setImportantInformation(array $important_information): void
    {
        $this->important_information = $important_information;
    }

    public function setUserRating(string $user_rating): void
    {
        $this->user_rating = $user_rating;
    }

    public function getUserRating(): string
    {
        return $this->user_rating;
    }

    public function setGiataDestination(string $giata_destination): void
    {
        $this->giata_destination = $giata_destination;
    }

    public function getGiataDestination(): string
    {
        return $this->giata_destination;
    }

    public function setAmenities(array $amenities): void
    {
        $this->amenities = $amenities;
    }

    public function getAmenities(): array
    {
        return $this->amenities;
    }

    public function setRating(string $rating): void
    {
        $this->rating = $rating;
    }

    public function getRating(): string
    {
        return $this->rating;
    }

    public function setLongitude(string $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function setLatitude(string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function setHotelName(string $hotel_name): void
    {
        $this->hotel_name = $hotel_name;
    }

    public function getHotelName(): string
    {
        return $this->hotel_name;
    }

    public function setDescription(array $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): array
    {
        return $this->description;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setGiataHotelCode(int $giata_hotel_code): void
    {
        $this->giata_hotel_code = $giata_hotel_code;
    }

    public function getGiataHotelCode(): int
    {
        return $this->giata_hotel_code;
    }

    public function toArray(): array
    {
        return [
            'giata_hotel_code' => $this->getGiataHotelCode(),
            'weight' => $this->getWeight(),
            'images' => $this->getImages(),
            'hotel_name' => $this->getHotelName(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'rating' => $this->getRating(),
            'currency' => $this->getCurrency(),
            'number_rooms' => $this->getNumberRooms(),
            'nearest_airports' => $this->getNearestAirports(),
            'description' => $this->getDescription(),
            'deposit_information' => $this->getDepositInformation(),
            'cancellation_policies' => $this->getCancellationPolicies(),
            'attributes' => array_values($this->getAmenities()),
            'ultimate_amenities' => $this->getUltimateAmenities(),
            'giata_destination' => $this->getGiataDestination(),
            'user_rating' => $this->getUserRating(),
            'drivers' => $this->getDrivers(),
        ];
    }
}
