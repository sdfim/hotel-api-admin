<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
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

    public function __construct(
        private readonly ImageGalleryDTO $imageGalleryDTO
    ) {}

    public function transform(Collection $promotions)
    {
        return $promotions->map(function ($promotion) {
            return $this->transformProductPromotion($promotion);
        })->all();
    }

    public function transformProductPromotion(ProductPromotion $promotion)
    {
        return [
            'id' => $promotion->id,
            'promotion_name' => $promotion->promotion_name,
            'description' => $promotion->description,
            'validity_start' => $promotion->validity_start,
            'validity_end' => $promotion->validity_end,
            'booking_start' => $promotion->booking_start,
            'booking_end' => $promotion->booking_end,
            'terms_conditions' => $promotion->terms_conditions,
            'exclusions' => $promotion->exclusions,
            'deposit_info' => $promotion->deposit_info,
            'galleries' => $this->imageGalleryDTO->transform($promotion->galleries),
        ];
    }
}
