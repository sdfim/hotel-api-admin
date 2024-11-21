<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\Hotel;

class HotelDTO
{
    public $product_id;
    public $weight;
    public $sale_type;
    public $address;
    public $star_rating;
    public $num_rooms;
    public $room_images_source;
    public $hotel_board_basis;
    public $rooms;
    public $web_finders;
    public $product;

    public function __construct(Hotel $hotel)
    {
        $this->product_id = $hotel->product_id;
        $this->weight = $hotel->weight;
        $this->sale_type = $hotel->sale_type;
        $this->address = $hotel->address;
        $this->star_rating = $hotel->star_rating;
        $this->num_rooms = $hotel->num_rooms;
        $this->room_images_source = new ContentSourceDTO($hotel->roomImagesSource);
        $this->hotel_board_basis = $hotel->hotel_board_basis;
        $this->rooms = $hotel->rooms->map(function ($room) {
            return new HotelRoomDTO($room);
        });
        $this->web_finders = $hotel->webFinders->map(function ($webFinder) {
            return new HotelWebFinderDTO($webFinder);
        });
        $this->product = new ProductDTO($hotel->product);
    }
}
