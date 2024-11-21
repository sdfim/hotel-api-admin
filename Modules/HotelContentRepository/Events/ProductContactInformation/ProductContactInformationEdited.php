<?php

namespace Modules\HotelContentRepository\Events\ProductContactInformation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ProductContactInformation;

class ProductContactInformationEdited
{
    use Dispatchable, SerializesModels;

    public $hotelContactInformation;

    public function __construct(ProductContactInformation $hotelContactInformation)
    {
        $this->hotelContactInformation = $hotelContactInformation;
    }
}
