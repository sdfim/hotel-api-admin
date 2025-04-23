<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\HotelRoom;

class RoomTransformer extends TransformerAbstract
{
    public function transform(HotelRoom $room)
    {
        return [
            'name' => $room->name,
            'area' => $room->area,
            'room_views' => $room->room_views,
            'bed_groups' => $room->bed_groups,
            'external_code' => $room->external_code,
            'description' => $room->description,
            'galleries' => $room->galleries,
        ];
    }
}
