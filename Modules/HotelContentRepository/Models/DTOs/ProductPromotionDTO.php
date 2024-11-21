<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductPromotion;

class ProductPromotionDTO
{
    public $id;
    public $product_id;
    public $promotion_name;
    public $description;
    public $validity_start;
    public $validity_end;
    public $booking_start;
    public $booking_end;
    public $terms_conditions;
    public $exclusions;
    public $deposit_info;
    public $galleries;

    public function __construct(ProductPromotion $promotion)
    {
        $this->id = $promotion->id;
        $this->product_id = $promotion->product_id;
        $this->promotion_name = $promotion->promotion_name;
        $this->description = $promotion->description;
        $this->validity_start = $promotion->validity_start;
        $this->validity_end = $promotion->validity_end;
        $this->booking_start = $promotion->booking_start;
        $this->booking_end = $promotion->booking_end;
        $this->terms_conditions = $promotion->terms_conditions;
        $this->exclusions = $promotion->exclusions;
        $this->deposit_info = $promotion->deposit_info;
        $this->galleries = $promotion->galleries->map(function ($gallery) {
            return new ImageGalleryDTO($gallery);
        });
    }
}
