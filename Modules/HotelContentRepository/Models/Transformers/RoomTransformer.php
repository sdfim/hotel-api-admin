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
            'hbsi_data_mapped_name' => $room->hbsi_data_mapped_name,
            'description' => $room->description,
            'galleries' => $room->galleries,
        ];
    }
}
