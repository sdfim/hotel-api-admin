<?php

namespace Modules\HotelContentRepository\Actions\HotelRate;

use Modules\HotelContentRepository\Models\HotelRate;

class AddHotelRate
{
    public function duplicate(HotelRate $record): HotelRate
    {
        $newRecord = $record->replicate();
        $newRecord->code = $newRecord->code.'-duplicate';
        $newRecord->save();

        return $newRecord;
    }
}
