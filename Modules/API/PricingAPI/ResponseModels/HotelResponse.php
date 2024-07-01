<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelResponse extends BaseResponse
{
    /**
     * @var float
     */
    private float $distanceFromSearchLocation;

    /**
     * @var int
     */
    private int $giata_hotel_id;
    /**
     * @var string
     */
    private string $supplier;
    /**
     * @var int
     */
    private string $supplier_hotel_id;
    /**
     * @var string
     */
    private string $destination;
    /**
     * @var string
     */
    private string $meal_plans_available;
    /**
     * @var string
     */
    private string $lowest_priced_room_group;
    /**
     * @var string
     */
    private string $pay_at_hotel_available;
    /**
     * @var string
     */
    private string $pay_now_available;
    /**
     * @var string
     */
    private string $non_refundable_rates;
    /**
     * @var string
     */
    private string $refundable_rates;
    /**
     * @var array
     */
    private array $room_groups;
    /**
     * @var string
     */
    private string $hotel_name;
    /**
     * @var string
     */
    private string $board_basis;

    private array $room_combinations = [];

    private string $rating;



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
     * @param array $room_combinations
     * @return void
     */
    public function setRoomCombinations(array $room_combinations): void
    {
        $this->room_combinations = $room_combinations;
    }

    /**
     * @return array
     */
    public function getRoomCombinations(): array
    {
        return $this->room_combinations;
    }

    /**
     * @return string
     */
    public function getHotelName(): string
    {
        return $this->hotel_name;
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
    public function getBoardBasis(): string
    {
        return $this->board_basis;
    }

    /**
     * @param string $board_basis
     * @return void
     */
    public function setBoardBasis(string $board_basis): void
    {
        $this->board_basis = $board_basis;
    }

    /**
     * @param int $giata_hotel_id
     * @return void
     */
    public function setGiataHotelId(int $giata_hotel_id): void
    {
        $this->giata_hotel_id = $giata_hotel_id;
    }

    /**
     * @return int
     */
    public function getGiataHotelId(): int
    {
        return $this->giata_hotel_id;
    }

    /**
     * @param string $supplier
     * @return void
     */
    public function setSupplier(string $supplier): void
    {
        $this->supplier = $supplier;
    }

    /**
     * @return string
     */
    public function getSupplier(): string
    {
        return $this->supplier;
    }

    /**
     * @param int $supplier_hotel_id
     * @return void
     */
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

    /**
     * @param string $destination
     * @return void
     */
    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $meal_plans_available
     * @return void
     */
    public function setMealPlansAvailable(string $meal_plans_available): void
    {
        $this->meal_plans_available = $meal_plans_available;
    }

    /**
     * @return string
     */
    public function getMealPlansAvailable(): string
    {
        return $this->meal_plans_available;
    }

    /**
     * @param string $lowest_priced_room_group
     * @return void
     */
    public function setLowestPricedRoomGroup(string $lowest_priced_room_group): void
    {
        $this->lowest_priced_room_group = $lowest_priced_room_group;
    }

    /**
     * @return string
     */
    public function getLowestPricedRoomGroup(): string
    {
        return $this->lowest_priced_room_group;
    }

    /**
     * @param string $pay_at_hotel_available
     * @return void
     */
    public function setPayAtHotelAvailable(string $pay_at_hotel_available): void
    {
        $this->pay_at_hotel_available = $pay_at_hotel_available;
    }

    /**
     * @return string
     */
    public function getPayAtHotelAvailable(): string
    {
        return $this->pay_at_hotel_available;
    }

    /**
     * @param string $pay_now_available
     * @return void
     */
    public function setPayNowAvailable(string $pay_now_available): void
    {
        $this->pay_now_available = $pay_now_available;
    }

    /**
     * @return string
     */
    public function getPayNowAvailable(): string
    {
        return $this->pay_now_available;
    }

    /**
     * @param string $non_refundable_rates
     * @return void
     */
    public function setNonRefundableRates(string $non_refundable_rates): void
    {
        $this->non_refundable_rates = $non_refundable_rates;
    }

    /**
     * @return string
     */
    public function getNonRefundableRates(): string
    {
        return $this->non_refundable_rates;
    }

    /**
     * @param string $refundable_rates
     * @return void
     */
    public function setRefundableRates(string $refundable_rates): void
    {
        $this->refundable_rates = $refundable_rates;
    }

    /**
     * @return string
     */
    public function getRefundableRates(): string
    {
        return $this->refundable_rates;
    }

    /**
     * @param array $room_groups
     * @return void
     */
    public function setRoomGroups(array $room_groups): void
    {
        $this->room_groups = $room_groups;
    }

    /**
     * @return array
     */
    public function getRoomGroups(): array
    {
        return $this->room_groups;
    }

    /**
     * @return float
     */
    public function getDistanceFromSearchLocation(): float
    {
        return $this->distanceFromSearchLocation;
    }

    /**
     * @param float $distanceFromSearchLocation
     */
    public function setDistanceFromSearchLocation(float $distanceFromSearchLocation): void
    {
        $this->distanceFromSearchLocation = $distanceFromSearchLocation;
    }

    /**
     * @return array
     */
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
        ];
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
