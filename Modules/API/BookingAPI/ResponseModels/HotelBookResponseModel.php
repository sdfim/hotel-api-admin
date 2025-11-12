<?php

namespace Modules\API\BookingAPI\ResponseModels;

class HotelBookResponseModel
{
    private string $status;

    private string $booking_id;

    private string $booking_item;

    private string $supplier;

    private string $hotel_name;

    private array $rooms;

    private bool $non_refundable = false;

    private array $cancellation_terms;

    private string $rate;

    private float $total_price;

    private float $total_tax;

    private float $total_fees;

    private float $total_net;

    private string $currency;

    private float $per_night_breakdown;

    private array $confirmation_numbers_list = [];

    private array $deposits = [];

    private array $amenities = [];

    public function setConfirmationNumbersList(array $confirmation_numbers_list): void
    {
        $this->confirmation_numbers_list = $confirmation_numbers_list;
    }

    public function getConfirmationNumbersList(): array
    {
        return $this->confirmation_numbers_list;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setBookingId(string $booking_id): void
    {
        $this->booking_id = $booking_id;
    }

    public function getBookingId(): string
    {
        return $this->booking_id;
    }

    public function setBookringItem(string $booking_item): void
    {
        $this->booking_item = $booking_item;
    }

    public function getBookringItem(): string
    {
        return $this->booking_item;
    }

    public function setSupplier(string $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function getSupplier(): string
    {
        return $this->supplier;
    }

    public function setHotelName(string $hotel_name): void
    {
        $this->hotel_name = $hotel_name;
    }

    public function getHotelName(): string
    {
        return $this->hotel_name;
    }

    public function setRooms(array $rooms): void
    {
        $this->rooms = $rooms;
    }

    public function getRooms(): array
    {
        return $this->rooms;
    }

    public function setNonRefundable(bool $non_refundable): void
    {
        $this->non_refundable = $non_refundable;
    }

    public function getNonRefundable(): bool
    {
        return $this->non_refundable ?? false;
    }

    public function setCancellationTerms(array $cancellation_terms): void
    {
        $this->cancellation_terms = $cancellation_terms;
    }

    public function getCancellationTerms(): array
    {
        return $this->cancellation_terms;
    }

    public function setRate(string $rate): void
    {
        $this->rate = $rate;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setTotalPrice(float $total_price): void
    {
        $this->total_price = $total_price;
    }

    public function getTotalPrice(): float
    {
        return $this->total_price;
    }

    public function setTotalTax(float $total_tax): void
    {
        $this->total_tax = $total_tax;
    }

    public function getTotalTax(): float
    {
        return $this->total_tax;
    }

    public function setTotalFees(float $total_fees): void
    {
        $this->total_fees = $total_fees;
    }

    public function getTotalFees(): float
    {
        return $this->total_fees;
    }

    public function setTotalNet(float $total_net): void
    {
        $this->total_net = $total_net;
    }

    public function getTotalNet(): float
    {
        return $this->total_net;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setPerNightBreakdown(float $per_night_breakdown): void
    {
        $this->per_night_breakdown = $per_night_breakdown;
    }

    public function getPerNightBreakdown(): float
    {
        return $this->per_night_breakdown;
    }

    public function setDeposits(array $deposits): void
    {
        $this->deposits = $deposits;
    }

    public function getDeposits(): array
    {
        return $this->deposits;
    }

    public function setAmenities(array $amenities): void
    {
        $this->amenities = $amenities;
    }

    public function getAmenities(): array
    {
        return $this->amenities;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->getStatus(),
            'booking_id' => $this->getBookingId(),
            'booking_item' => $this->getBookringItem(),
            'supplier' => $this->getSupplier(),
            'hotel_name' => $this->getHotelName(),
            'rooms' => $this->getRooms(),
            'non_refundable' => $this->getNonRefundable(),
            'cancellation_terms' => $this->getCancellationTerms(),
            'rate' => $this->getRate(),
            'total_price' => $this->getTotalPrice(),
            'total_tax' => $this->getTotalTax(),
            'total_fees' => $this->getTotalFees(),
            'total_net' => $this->getTotalNet(),
            'currency' => $this->getCurrency(),
            'per_night_breakdown' => $this->getPerNightBreakdown(),
            'confirmation_numbers_list' => $this->getConfirmationNumbersList(),
            'deposits' => $this->getDeposits(),
            'amenities' => $this->getAmenities(),
        ];
    }
}
