<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailRoomsResponse
{
    /**
     * @var int
     */
    private int $supplier_room_id;
    /**
     * @var string
     */
    private string $supplier_room_name;
    /**
     * @var array
     */
    private array $amenities;
    /**
     * @var array
     */
    private array $images;
    /**
     * @var string
     */
    private string $descriptions;

    /**
     * @param string $descriptions
     * @return void
     */
    public function setDescriptions(string $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    /**
     * @return string
     */
    public function getDescriptions(): string
    {
        return $this->descriptions;
    }

    /**
     * @param array $images
     * @return void
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param array $amenities
     * @return void
     */
    public function setAmenities(array $amenities): void
    {
        $this->amenities = $amenities;
    }

    /**
     * @return array
     */
    public function getAmenities(): array
    {
        return $this->amenities;
    }

    /**
     * @param string $supplier_room_name
     * @return void
     */
    public function setSupplierRoomName(string $supplier_room_name): void
    {
        $this->supplier_room_name = $supplier_room_name;
    }

    /**
     * @return string
     */
    public function getSupplierRoomName(): string
    {
        return $this->supplier_room_name;
    }

    /**
     * @param int $supplier_room_id
     * @return void
     */
    public function setSupplierRoomId(int $supplier_room_id): void
    {
        $this->supplier_room_id = $supplier_room_id;
    }

    /**
     * @return int
     */
    public function getSupplierRoomId(): int
    {
        return $this->supplier_room_id;
    }

    /**
     * @return array
     */
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
