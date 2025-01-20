<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\HotelRoom;
use Illuminate\Support\Collection;

class HotelRoomDTO
{
    public $id;
    public $hotel_id;
    public $hbsi_data_mapped_name;
    public $name;
    public $description;
    public $amenities;
    public $occupancy;
    public $bed_groups;
    public $area;
    public $galleries;

    public function __construct(
        private readonly ImageGalleryDTO $imageGalleryDTO,
    ) {}

    public function transform(Collection $hotelRooms)
    {
        return $hotelRooms->map(function ($room) {
            return $this->transformRoom($room);
        })->all();
    }

    private function transformRoom(HotelRoom $hotelRoom)
    {
        return [
            'id' => $hotelRoom->id,
            'hbsi_data_mapped_name' => $hotelRoom->hbsi_data_mapped_name,
            'name' => $hotelRoom->name,
            'area' => $hotelRoom->area,
            'room_views' => $hotelRoom->room_views,
            'description' => $hotelRoom->description,
            'amenities' => $hotelRoom->amenities,
            'occupancy' => $hotelRoom->occupancy,
            'bed_groups' => $hotelRoom->bed_groups,
            'galleries' => $this->imageGalleryDTO->transform($hotelRoom->galleries),
        ];
    }
}
