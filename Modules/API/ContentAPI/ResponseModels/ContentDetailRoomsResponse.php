<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailRoomsResponse
{
    private string $content_supplier;

    private string $unified_room_code;

    private int $supplier_room_id;

    private string $supplier_room_name;

    private string $supplier_room_code;

    private array $amenities;

    private array $images;

    private string $descriptions;

    private array $related_rooms;

    private array $ultimate_amenities;

    public function setUltimateAmenities(array $ultimate_amenities): void
    {
        $this->ultimate_amenities = $ultimate_amenities;
    }

    public function getUltimateAmenities(): array
    {
        return $this->ultimate_amenities;
    }

    public function setRelatedRooms(array $related_rooms): void
    {
        $this->related_rooms = $related_rooms;
    }

    public function getRelatedRooms(): array
    {
        return $this->related_rooms;
    }

    public function setContentSupplier(string $content_supplier): void
    {
        $this->content_supplier = $content_supplier;
    }

    public function getContentSupplier(): string
    {
        return $this->content_supplier;
    }

    public function setUnifiedRoomCode(string $unified_room_code): void
    {
        $this->unified_room_code = $unified_room_code;
    }

    public function getUnifiedRoomCode(): string
    {
        return $this->unified_room_code;
    }

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

    public function setSupplierRoomCode(string $supplier_room_code): void
    {
        $this->supplier_room_code = $supplier_room_code;
    }

    public function getSupplierRoomCode(): string
    {
        return $this->supplier_room_code;
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
            'content_supplier' => $this->getContentSupplier(),
            'unified_room_code' => $this->getUnifiedRoomCode(),
            'supplier_room_id' => $this->getSupplierRoomId(),
            'supplier_room_name' => $this->getSupplierRoomName(),
            'supplier_room_code' => $this->getSupplierRoomCode(),
            'attributes' => $this->getAmenities(),
            'ultimate_amenities' => $this->getUltimateAmenities(),
            'images' => $this->getImages(),
            'descriptions' => $this->getDescriptions(),
            'connecting_room_types' => $this->getRelatedRooms(),
        ];
    }
}
