<?php

namespace Modules\API\BookingAPI\ResponseModels;

class HotelBookResponseModel
{
    /**
     * @var string
     */
    private string $status;
    /**
     * @var string
     */
    private string $booking_id;
    /**
     * @var string
     */
    private string $booking_item;
    /**
     * @var string
     */
    private string $supplier;
    /**
     * @var string
     */
    private string $hotel_name;
    /**
     * @var array
     */
    private array $rooms;
    /**
     * @var string
     */
    private string $cancellation_terms;
    /**
     * @var string
     */
    private string $rate;
    /**
     * @var float
     */
    private float $total_price;
    /**
     * @var float
     */
    private float $total_tax;
    /**
     * @var float
     */
    private float $total_fees;
    /**
     * @var float
     */
    private float $total_net;
    /**
     * @var float
     */
    private float $affiliate_service_charge;
    /**
     * @var string
     */
    private string $currency;
    /**
     * @var string
     */
    private float $per_night_breakdown;

    /**
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $booking_id
     * @return void
     */
    public function setBookingId(string $booking_id): void
    {
        $this->booking_id = $booking_id;
    }

    /**
     * @return string
     */
    public function getBookingId(): string
    {
        return $this->booking_id;
    }

    /**
     * @param string $booking_item
     * @return void
     */
    public function setBookringItem(string $booking_item): void
    {
        $this->booking_item = $booking_item;
    }

    /**
     * @return string
     */
    public function getBookringItem(): string
    {
        return $this->booking_item;
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
     * @param string $cancellation_terms
     * @return void
     */
    public function setCancellationTerms(string $cancellation_terms): void
    {
        $this->cancellation_terms = $cancellation_terms;
    }

    /**
     * @return string
     */
    public function getCancellationTerms(): string
    {
        return $this->cancellation_terms;
    }

    /**
     * @param string $rate
     * @return void
     */
    public function setRate(string $rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return string
     */
    public function getRate(): string
    {
        return $this->rate;
    }

    /**
     * @param float $total_price
     * @return void
     */
    public function setTotalPrice(float $total_price): void
    {
        $this->total_price = $total_price;
    }

    /**
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->total_price;
    }

    /**
     * @param float $total_tax
     * @return void
     */
    public function setTotalTax(float $total_tax): void
    {
        $this->total_tax = $total_tax;
    }

    /**
     * @return float
     */
    public function getTotalTax(): float
    {
        return $this->total_tax;
    }

    /**
     * @param float $total_fees
     * @return void
     */
    public function setTotalFees(float $total_fees): void
    {
        $this->total_fees = $total_fees;
    }

    /**
     * @return float
     */
    public function getTotalFees(): float
    {
        return $this->total_fees;
    }

    /**
     * @param float $total_net
     * @return void
     */
    public function setTotalNet(float $total_net): void
    {
        $this->total_net = $total_net;
    }

    /**
     * @return float
     */
    public function getTotalNet(): float
    {
        return $this->total_net;
    }

    /**
     * @param float $affiliate_service_charge
     * @return void
     */
    public function setAffiliateServiceCharge(float $affiliate_service_charge): void
    {
        $this->affiliate_service_charge = $affiliate_service_charge;
    }

    /**
     * @return float
     */
    public function getAffiliateServiceCharge(): float
    {
        return $this->affiliate_service_charge;
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param float $per_night_breakdown
     * @return void
     */
    public function setPerNightBreakdown(float $per_night_breakdown): void
    {
        $this->per_night_breakdown = $per_night_breakdown;
    }

    /**
     * @return float
     */
    public function getPerNightBreakdown(): float
    {
        return $this->per_night_breakdown;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->getStatus(),
            'booking_id' => $this->getBookingId(),
            'booking_item' => $this->getBookringItem(),
            'supplier' => $this->getSupplier(),
            'hotel_name' => $this->getHotelName(),
            'rooms' => $this->getRooms(),
            'cancellation_terms' => $this->getCancellationTerms(),
            'rate' => $this->getRate(),
            'total_price' => $this->getTotalPrice(),
            'total_tax' => $this->getTotalTax(),
            'total_fees' => $this->getTotalFees(),
            'total_net' => $this->getTotalNet(),
            'affiliate_service_charge' => $this->getAffiliateServiceCharge(),
            'currency' => $this->getCurrency(),
            'per_night_breakdown' => $this->getPerNightBreakdown(),
        ];
    }

}
