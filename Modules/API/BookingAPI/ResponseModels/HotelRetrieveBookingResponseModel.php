<?php

namespace Modules\API\BookingAPI\ResponseModels;

class HotelRetrieveBookingResponseModel extends HotelBookResponseModel
{
    /**
     * @var string
     */
    private string $room_name;
    /**
     * @var string
     */
    private string $room_type;
    /**
     * @var string
     */
    private string $board_basis;
    /**
     * @var array
     */
    private array $confirmation_numbers = [];
    /**
     * @var array
     */
    private array $query;
    /**
     * @var string
     */
    private string $supplier_book_id;
    /**
     * @var array
     */
    private array $billing_contact;
    /**
     * @var string
     */
    private string $billing_email;
    /**
     * @var array
     */
    private array $billing_phone;

    /**
     * @return array
     */
    public function getBillingContact(): array
    {
        return $this->billing_contact;
    }

    /**
     * @param array $billing_contact
     */
    public function setBillingContact(array $billing_contact): void
    {
        $this->billing_contact = $billing_contact;
    }

    /**
     * @return string
     */
    public function getBillingEmail(): string
    {
        return $this->billing_email;
    }

    /**
     * @param string $billing_email
     */
    public function setBillingEmail(string $billing_email): void
    {
        $this->billing_email = $billing_email;
    }

    /**
     * @return array
     */
    public function getBillingPhone(): array
    {
        return $this->billing_phone;
    }

    /**
     * @param array $billing_phone
     */
    public function setBillingPhone(array $billing_phone): void
    {
        $this->billing_phone = $billing_phone;
    }

    /**
     * @return string
     */
    public function getSupplierBookId(): string
    {
        return $this->supplier_book_id;
    }

    /**
     * @param string $supplier_book_id
     */
    public function setSupplierBookId(string $supplier_book_id): void
    {
        $this->supplier_book_id = $supplier_book_id;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getRoomName(): string
    {
        return $this->room_name;
    }

    /**
     * @param string $room_name
     */
    public function setRoomName(string $room_name): void
    {
        $this->room_name = $room_name;
    }

    /**
     * @return string
     */
    public function getRoomType(): string
    {
        return $this->room_type;
    }

    /**
     * @param string $room_type
     */
    public function setRoomType(string $room_type): void
    {
        $this->room_type = $room_type;
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
     */
    public function setBoardBasis(string $board_basis): void
    {
        $this->board_basis = $board_basis;
    }

    /**
     * @return array
     */
    public function getConfirmationNumbers(): array
    {
        return $this->confirmation_numbers;
    }

    /**
     * @param array $confirmation_numbers
     */
    public function setConfirmationNumbers(array $confirmation_numbers): void
    {
        $this->confirmation_numbers = $confirmation_numbers;
    }

    /**
     * @return array
     */
    public function toRetrieveArray(): array
    {
        return array_merge(
            $this->toArray(),
            [
                'confirmation_numbers_list' => $this->getConfirmationNumbers(),
                'hotel_name' => $this->getHotelName(),
//                'room_name' => $this->getRoomName(),
//                'room_type' => $this->getRoomType(),
                'board_basis' => $this->getBoardBasis(),
                'rooms' => $this->getRooms(),
                'supplier_book_id' => $this->getSupplierBookId(),
                'billing_contact' => $this->getBillingContact(),
                'billing_email' => $this->getBillingEmail(),
                'billing_phone' => $this->getBillingPhone(),
                'query' => $this->getQuery(),
            ]
        );
    }
}
