<?php

namespace Modules\HotelContentRepository\Actions\ProductContactInformation;

use Modules\HotelContentRepository\Events\ProductContactInformation\ProductContactInformationDeleted;
use Modules\HotelContentRepository\Models\ProductContactInformation;

class DeleteProductContactInformation
{
    public function handle(ProductContactInformation $hotelContactInformation)
    {
        $hotelContactInformation->delete();
        ProductContactInformationDeleted::dispatch($hotelContactInformation);
    }
}
