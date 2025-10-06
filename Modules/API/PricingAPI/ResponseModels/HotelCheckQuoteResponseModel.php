<?php

namespace Modules\API\PricingAPI\ResponseModels;

class HotelCheckQuoteResponseModel
{
    private array $comparison_of_amounts = [];
    private $check_quote_search_id;
    private ?string $hotel_image = null;
    private array $attributes = [];
    private $email_verification;
    private array $check_quote_search_query = [];
    private $giata_id;
    private $booking_item;
    private $booking_id;
    private array $current_search = [];
    private array $first_search = [];

    public function setComparisonOfAmounts(array $comparison_of_amounts): void
    {
        $this->comparison_of_amounts = $comparison_of_amounts;
    }
    public function getComparisonOfAmounts(): array
    {
        return $this->comparison_of_amounts;
    }

    public function setCheckQuoteSearchId($check_quote_search_id): void
    {
        $this->check_quote_search_id = $check_quote_search_id;
    }
    public function getCheckQuoteSearchId()
    {
        return $this->check_quote_search_id;
    }

    public function setHotelImage(?string $hotel_image): void
    {
        $this->hotel_image = $hotel_image;
    }
    public function getHotelImage(): ?string
    {
        return $this->hotel_image;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setEmailVerification($email_verification): void
    {
        $this->email_verification = $email_verification;
    }
    public function getEmailVerification()
    {
        return $this->email_verification;
    }

    public function setCheckQuoteSearchQuery(array $check_quote_search_query): void
    {
        $this->check_quote_search_query = $check_quote_search_query;
    }
    public function getCheckQuoteSearchQuery(): array
    {
        return $this->check_quote_search_query;
    }

    public function setGiataId($giata_id): void
    {
        $this->giata_id = $giata_id;
    }
    public function getGiataId()
    {
        return $this->giata_id;
    }

    public function setBookingItem($booking_item): void
    {
        $this->booking_item = $booking_item;
    }
    public function getBookingItem()
    {
        return $this->booking_item;
    }

    public function setBookingId($booking_id): void
    {
        $this->booking_id = $booking_id;
    }
    public function getBookingId()
    {
        return $this->booking_id;
    }

    public function setCurrentSearch(array $current_search): void
    {
        $this->current_search = $current_search;
    }
    public function getCurrentSearch(): array
    {
        return $this->current_search;
    }

    public function setFirstSearch(array $first_search): void
    {
        $this->first_search = $first_search;
    }
    public function getFirstSearch(): array
    {
        return $this->first_search;
    }

    public function toArray(): array
    {
        return [
            'comparison_of_amounts' => $this->getComparisonOfAmounts(),
            'check_quote_search_id' => $this->getCheckQuoteSearchId(),
            'hotel_image' => $this->getHotelImage(),
            'attributes' => $this->getAttributes(),
            'email_verification' => $this->getEmailVerification(),
            'check_quote_search_query' => $this->getCheckQuoteSearchQuery(),
            'giata_id' => $this->getGiataId(),
            'booking_item' => $this->getBookingItem(),
            'booking_id' => $this->getBookingId(),
            'current_search' => $this->getCurrentSearch(),
            'first_search' => $this->getFirstSearch(),
        ];
    }
}
