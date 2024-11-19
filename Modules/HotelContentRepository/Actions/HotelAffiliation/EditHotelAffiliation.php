<?php

namespace Modules\HotelContentRepository\Actions\HotelAffiliation;

use Modules\HotelContentRepository\API\Requests\HotelAffiliationRequest;
use Modules\HotelContentRepository\Events\HotelAffiliation\HotelAffiliationEdited;
use Modules\HotelContentRepository\Models\HotelAffiliation;

class EditHotelAffiliation
{
    public function handle(HotelAffiliation $hotelAffiliation, HotelAffiliationRequest $request)
    {
        $hotelAffiliation->update($request->validated());
        HotelAffiliationEdited::dispatch($hotelAffiliation);
        return $hotelAffiliation;
    }
}
