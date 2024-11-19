<?php

namespace Modules\HotelContentRepository\Actions\HotelAffiliation;

use Modules\HotelContentRepository\API\Requests\HotelAffiliationRequest;
use Modules\HotelContentRepository\Events\HotelAffiliation\HotelAffiliationAdded;
use Modules\HotelContentRepository\Models\HotelAffiliation;

class AddHotelAffiliation
{
    public function handle(HotelAffiliationRequest $request)
    {
        $hotelAffiliation = HotelAffiliation::create($request->validated());
        HotelAffiliationAdded::dispatch($hotelAffiliation);
        return $hotelAffiliation;
    }
}
