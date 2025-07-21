<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\Hotel;

class HotelDTO
{
    public $weight;

    public $sale_type;

    public $address;

    public $star_rating;

    public $num_rooms;

    public $room_images_source;

    public $hotel_board_basis;

    public $travel_agent_commission;

    public $rooms;

    public $web_finders;

    public $product;

    public $holdable;

    public function __construct(
        private readonly HotelRoomDTO $hotelRoomDTO,
        private readonly HotelWebFinderDTO $hotelWebFinderDTO,
        private readonly ProductDTO $productDTO,
    ) {}

    public function transform(Collection $hotels, bool $returnProduct = false)
    {
        return $hotels->map(function ($hotel) use ($returnProduct) {
            return $this->transformHotel($hotel, $returnProduct);
        })->all();
    }

    public function transformHotel(Hotel $hotel, bool $returnProduct = false)
    {
        $data = [
            'weight' => $hotel->weight,
            'sale_type' => $hotel->sale_type,
            'address' => $hotel->address,
            'star_rating' => $hotel->star_rating,
            'num_rooms' => $hotel->num_rooms,
            'room_images_source' => resolve(ContentSourceDTO::class)->transformContentSource($hotel->roomImagesSource),
            'hotel_board_basis' => $hotel->hotel_board_basis,
            'travel_agent_commission' => $hotel->travel_agent_commission,
            'rooms' => $this->hotelRoomDTO->transform($hotel->rooms),
            'web_finders' => $this->hotelWebFinderDTO->transform($hotel->webFinders),
            'holdable' => $hotel->holdable,
        ];

        if ($returnProduct) {
            $data['product'] = $this->productDTO->transformProduct($hotel->product);
        }

        return $data;
    }
}
