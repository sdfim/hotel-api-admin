<?php

namespace Modules\HotelContentRepository\Actions\HotelRoom;

use Modules\HotelContentRepository\Models\HotelRoom;

class EditHotelRoom
{
    public function execute(HotelRoom $record, array $data): void
    {
        if (isset($data['supplier_codes'])) {
            $data['supplier_codes'] = json_encode($data['supplier_codes']);
        }
        $record->update($data);
        if (isset($data['attributes'])) {
            $record->attributes()->sync($data['attributes']);
        }
        if (isset($data['galleries'])) {
            $record->galleries()->sync($data['galleries']);
        }
        if (isset($data['related_rooms'])) {
            $record->relatedRooms()->sync($data['related_rooms']);
        }
    }
}
