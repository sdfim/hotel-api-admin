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
    }
}
