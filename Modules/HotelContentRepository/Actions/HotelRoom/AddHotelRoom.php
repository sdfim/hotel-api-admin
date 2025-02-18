<?php

namespace Modules\HotelContentRepository\Actions\HotelRoom;

use Modules\HotelContentRepository\Models\HotelRoom;

class AddHotelRoom
{
    /**
     * @param array $data
     * @param int|null $hotelId
     * @return HotelRoom|null
     */
    public function create(array $data, ?int $hotelId = null): ?HotelRoom
    {
        if ($hotelId) {
            $data['hotel_id'] = $hotelId;
        }
        if (isset($data['supplier_codes'])) {
            $data['supplier_codes'] = json_encode($data['supplier_codes']);
        }
        $hotelRoom = HotelRoom::create($data);
        if ($hotelRoom) {
            if (isset($data['attributes'])) {
                $hotelRoom->attributes()->sync($data['attributes']);
            }
            if (isset($data['galleries'])) {
                $hotelRoom->galleries()->sync($data['galleries']);
            }
            if (isset($data['related_rooms'])) {
                $hotelRoom->relatedRooms()->sync($data['related_rooms']);
            }
        }

        return $hotelRoom;
    }
}
