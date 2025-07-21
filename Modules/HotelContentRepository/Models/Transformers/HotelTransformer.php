<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\Hotel;

class HotelTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'rooms',
        'webFinders',
        'product',
    ];

    public function transform(Hotel $hotel)
    {
        return [
            'id' => $hotel->id,
            'weight' => $hotel->weight,
            'sale_type' => $hotel->sale_type,
            'address' => $hotel->address,
            'star_rating' => $hotel->star_rating,
            'num_rooms' => $hotel->num_rooms,
            'room_images_source_id' => $hotel->room_images_source_id,
            'hotel_board_basis' => $hotel->hotel_board_basis,
            'travel_agent_commission' => $hotel->travel_agent_commission,
            'holdable' => $hotel->holdable,
        ];
    }

    public function includeRooms(Hotel $hotel)
    {
        return $this->collection($hotel->rooms, new RoomTransformer);
    }

    public function includeWebFinders(Hotel $hotel)
    {
        return $this->collection($hotel->webFinders, new WebFinderTransformer);
    }

    public function includeProduct(Hotel $hotel)
    {
        if ($hotel->product) {
            return $this->item($hotel->product, new ProductWithoutRelationTransformer);
        }

        return null;
    }
}
