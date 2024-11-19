<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\HotelPromotion;

class PromotionTransformer extends TransformerAbstract
{
    public function transform(HotelPromotion $promotion)
    {
        return [
            'promotion_name' => $promotion->promotion_name,
            'description' => $promotion->description,
            'validity_start' => $promotion->validity_start,
            'validity_end' => $promotion->validity_end,
            'booking_start' => $promotion->booking_start,
            'booking_end' => $promotion->booking_end,
            'terms_conditions' => $promotion->terms_conditions,
            'exclusions' => $promotion->exclusions,
            'deposit_info' => $promotion->deposit_info,
            'galleries' => $promotion->galleries,
        ];
    }
}
