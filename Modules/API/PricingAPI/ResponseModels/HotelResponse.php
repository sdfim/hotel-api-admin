<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelResponse
{
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
    private int $supplier_hotel_id;
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
    public function setSupplierHotelId(int $supplier_hotel_id): void
    {
        $this->supplier_hotel_id = $supplier_hotel_id;
    }

    /**
     * @return int
     */
    public function getSupplierHotelId(): int
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
     * @return array
     */
    public function toArray(): array
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
