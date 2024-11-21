<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\Hotel;

class HotelDTO extends HotelForProductDTO
{
    public $product;

    public function __construct(Hotel $hotel)
    {
        parent::__construct($hotel);
        $this->product = new ProductForRelationDTO($hotel->product);
    }
}
