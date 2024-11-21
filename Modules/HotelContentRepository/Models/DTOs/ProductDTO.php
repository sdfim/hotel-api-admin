<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\DTOs\HotelForProductDTO;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;

class ProductDTO extends ProductForRelationDTO
{
    public $related;

    public function __construct(Product $product)
    {
        parent::__construct($product);

        if ($product->related instanceof Hotel) {
            $this->related = new HotelForProductDTO($product->related);
        } else {
            $this->related = null; // Handle other related types as needed
        }
    }
}
