<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\HotelRoom;

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
    public $galleries;

    public function __construct(HotelRoom $hotelRoom)
    {
        $this->id = $hotelRoom->id;
        $this->hotel_id = $hotelRoom->hotel_id;
        $this->hbsi_data_mapped_name = $hotelRoom->hbsi_data_mapped_name;
        $this->name = $hotelRoom->name;
        $this->description = $hotelRoom->description;
        $this->amenities = $hotelRoom->amenities;
        $this->occupancy = $hotelRoom->occupancy;
        $this->bed_groups = $hotelRoom->bed_groups;
        $this->galleries = $hotelRoom->galleries->map(function ($gallery) {
            return new ImageGalleryDTO($gallery);
        });
    }
}
