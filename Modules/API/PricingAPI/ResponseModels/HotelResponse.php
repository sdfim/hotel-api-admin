<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelResponse extends BaseResponse
{
    private float $distanceFromSearchLocation;

    private int $giata_hotel_id;

    private string $supplier;

    private string $supplier_hotel_id;

    private string $destination;

    private string $meal_plans_available;

    private string $lowest_priced_room_group;

    private string $pay_at_hotel_available;

    private string $pay_now_available;

    private string $non_refundable_rates;

    private string $refundable_rates;

    private array $room_groups;

    private string $hotel_name;

    private string $board_basis;

    private array $room_combinations = [];

    private array $deposit_information = [];

    private string $rating;

    public function setRating(string $rating): void
    {
        $this->rating = $rating;
    }

    public function getRating(): string
    {
        return $this->rating;
    }

    public function setRoomCombinations(array $room_combinations): void
    {
        $this->room_combinations = $room_combinations;
    }

    public function getRoomCombinations(): array
    {
        return $this->room_combinations;
    }

    public function getHotelName(): string
    {
        return $this->hotel_name;
    }

    public function setHotelName(string $hotel_name): void
    {
        $this->hotel_name = $hotel_name;
    }

    public function getBoardBasis(): string
    {
        return $this->board_basis;
    }

    public function setBoardBasis(string $board_basis): void
    {
        $this->board_basis = $board_basis;
    }

    public function setGiataHotelId(int $giata_hotel_id): void
    {
        $this->giata_hotel_id = $giata_hotel_id;
    }

    public function getGiataHotelId(): int
    {
        return $this->giata_hotel_id;
    }

    public function setSupplier(string $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function getSupplier(): string
    {
        return $this->supplier;
    }

    public function setSupplierHotelId(string $supplier_hotel_id): void
    {
        $this->supplier_hotel_id = $supplier_hotel_id;
    }

    /**
     * @return int
     */
    public function getSupplierHotelId(): string
    {
        return $this->supplier_hotel_id;
    }

    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }

    public function setDepositInformation(array $depositInformation): void
    {
        $this->deposit_information = $depositInformation;
    }

    public function getDepositInformation(): array
    {
        return $this->deposit_information;
    }
    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setMealPlansAvailable(string $meal_plans_available): void
    {
        $this->meal_plans_available = $meal_plans_available;
    }

    public function getMealPlansAvailable(): string
    {
        return $this->meal_plans_available;
    }

    public function setLowestPricedRoomGroup(string $lowest_priced_room_group): void
    {
        $this->lowest_priced_room_group = $lowest_priced_room_group;
    }

    public function getLowestPricedRoomGroup(): string
    {
        return $this->lowest_priced_room_group;
    }

    public function setPayAtHotelAvailable(string $pay_at_hotel_available): void
    {
        $this->pay_at_hotel_available = $pay_at_hotel_available;
    }

    public function getPayAtHotelAvailable(): string
    {
        return $this->pay_at_hotel_available;
    }

    public function setPayNowAvailable(string $pay_now_available): void
    {
        $this->pay_now_available = $pay_now_available;
    }

    public function getPayNowAvailable(): string
    {
        return $this->pay_now_available;
    }

    public function setNonRefundableRates(string $non_refundable_rates): void
    {
        $this->non_refundable_rates = $non_refundable_rates;
    }

    public function getNonRefundableRates(): string
    {
        return $this->non_refundable_rates;
    }

    public function setRefundableRates(string $refundable_rates): void
    {
        $this->refundable_rates = $refundable_rates;
    }

    public function getRefundableRates(): string
    {
        return $this->refundable_rates;
    }

    public function setRoomGroups(array $room_groups): void
    {
        $this->room_groups = $room_groups;
    }

    public function getRoomGroups(): array
    {
        return $this->room_groups;
    }

    public function getDistanceFromSearchLocation(): float
    {
        return $this->distanceFromSearchLocation;
    }

    public function setDistanceFromSearchLocation(float $distanceFromSearchLocation): void
    {
        $this->distanceFromSearchLocation = $distanceFromSearchLocation;
    }

    public function toArray(): array
    {
        return [
            'distance' => $this->getDistanceFromSearchLocation(),
            'giata_hotel_id' => $this->getGiataHotelId(),
            'rating' => $this->getRating(),
            'hotel_name' => $this->getHotelName(),
            'board_basis' => $this->getBoardBasis(),
            'supplier' => $this->getSupplier(),
            'supplier_hotel_id' => $this->getSupplierHotelId(),
            'destination' => $this->getDestination(),
            'meal_plans_available' => $this->getMealPlansAvailable(),
            'lowest_priced_room_group' => $this->getLowestPricedRoomGroup(),
            'pay_at_hotel_available' => $this->getPayAtHotelAvailable(),
            'pay_now_available' => $this->getPayNowAvailable(),
            'non_refundable_rates' => $this->getNonRefundableRates(),
            'refundable_rates' => $this->getRefundableRates(),
            'room_groups' => $this->getRoomGroups(),
            'room_combinations' => $this->getRoomCombinations(),
            'deposit_information' => $this->getDepositInformation(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
