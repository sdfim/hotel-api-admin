<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailRoomsResponse
{
    private int $supplier_room_id;

    private string $supplier_room_name;

    private array $amenities;

    private array $images;

    private string $descriptions;

    public function setDescriptions(string $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    public function getDescriptions(): string
    {
        return $this->descriptions;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setAmenities(array $amenities): void
    {
        $this->amenities = $amenities;
    }

    public function getAmenities(): array
    {
        return $this->amenities;
    }

    public function setSupplierRoomName(string $supplier_room_name): void
    {
        $this->supplier_room_name = $supplier_room_name;
    }

    public function getSupplierRoomName(): string
    {
        return $this->supplier_room_name;
    }

    public function setSupplierRoomId(int $supplier_room_id): void
    {
        $this->supplier_room_id = $supplier_room_id;
    }

    public function getSupplierRoomId(): int
    {
        return $this->supplier_room_id;
    }

    public function toArray(): array
    {
        return [
            'supplier_room_id' => $this->getSupplierRoomId(),
            'supplier_room_name' => $this->getSupplierRoomName(),
            'amenities' => $this->getAmenities(),
            'images' => $this->getImages(),
            'descriptions' => $this->getDescriptions(),
        ];
    }
}
