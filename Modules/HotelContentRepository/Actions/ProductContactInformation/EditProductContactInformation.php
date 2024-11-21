<?php

namespace Modules\HotelContentRepository\Actions\ProductContactInformation;

use Modules\HotelContentRepository\API\Requests\ProductContactInformationRequest;
use Modules\HotelContentRepository\Events\ProductContactInformation\ProductContactInformationEdited;
use Modules\HotelContentRepository\Models\ProductContactInformation;

class EditProductContactInformation
{
    public function handle(ProductContactInformation $hotelContactInformation, ProductContactInformationRequest $request)
    {
        $hotelContactInformation->update($request->validated());
        ProductContactInformationEdited::dispatch($hotelContactInformation);
        return $hotelContactInformation;
    }
}
