<?php

namespace Modules\HotelContentRepository\Events\ProductDepositInformation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationDeleted
{
    use Dispatchable, SerializesModels;

    public $hotelDepositInformation;

    public function __construct(ProductDepositInformation $hotelDepositInformation)
    {
        $this->hotelDepositInformation = $hotelDepositInformation;
    }
}
