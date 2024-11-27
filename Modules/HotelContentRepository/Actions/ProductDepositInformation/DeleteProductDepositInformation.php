<?php

namespace Modules\HotelContentRepository\Actions\ProductDepositInformation;

use Modules\HotelContentRepository\Events\ProductDepositInformation\ProductDepositInformationDeleted;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class DeleteProductDepositInformation
{
    public function handle(ProductDepositInformation $hotelDepositInformation)
    {
        $hotelDepositInformation->delete();
        ProductDepositInformationDeleted::dispatch($hotelDepositInformation);
    }
}
