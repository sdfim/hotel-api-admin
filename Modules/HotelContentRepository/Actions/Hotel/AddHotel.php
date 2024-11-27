<?php

namespace Modules\HotelContentRepository\Actions\Hotel;

use Modules\HotelContentRepository\API\Requests\HotelRequest;
use Modules\HotelContentRepository\Events\Hotel\HotelAdded;
use Modules\HotelContentRepository\Models\Hotel;

class AddHotel
{
    public function handle(HotelRequest $request)
    {
        $hotel = Hotel::create($request->validated());
        HotelAdded::dispatch($hotel);
        return $hotel;
    }
}
