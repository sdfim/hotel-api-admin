<?php

namespace Modules\HotelContentRepository\Actions\HotelRate;

use Modules\HotelContentRepository\Models\HotelRate;

class UpdateHotelRate
{
    public function execute(HotelRate $hotelRate, array $data): void
    {
        $hotelRate->fill($data);
        $hotelRate->save();

        if (isset($data['dates'])) {
            $hotelRate->dates()->delete();
            foreach ($data['dates'] as $date) {
                $hotelRate->dates()->create($date);
            }
        }

        $validRoomIds = $hotelRate->rooms()->select('pd_hotel_rooms.id')->pluck('id')->toArray();
        if (! empty($validRoomIds)) {
            $hotelRate->load('productAffiliations.amenities');
            $hotelRate->productAffiliations->each(function ($affiliation) use ($validRoomIds) {
                $affiliation->amenities->each(function ($amenity) use ($validRoomIds) {
                    $filteredRooms = array_values(array_intersect($amenity->priority_rooms, $validRoomIds));
                    $amenity->update(['priority_rooms' => $filteredRooms]);
                });
            });
        }
    }
}
