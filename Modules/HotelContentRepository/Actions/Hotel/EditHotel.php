<?php

namespace Modules\HotelContentRepository\Actions\Hotel;

use Modules\HotelContentRepository\API\Requests\HotelRequest;
use Modules\HotelContentRepository\Events\Hotel\HotelEdited;
use Modules\HotelContentRepository\Models\Hotel;

class EditHotel
{
    public function handle(Hotel $hotel, HotelRequest $request)
    {
        $hotel->update($request->validated());
        HotelEdited::dispatch($hotel);

        return $hotel;
    }
}
