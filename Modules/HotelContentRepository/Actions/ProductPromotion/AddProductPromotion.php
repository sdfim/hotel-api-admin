<?php

namespace Modules\HotelContentRepository\Actions\ProductPromotion;

use Modules\HotelContentRepository\Models\ProductPromotion;

class AddProductPromotion
{
    public function createWithGalleries(array $data): ProductPromotion
    {
        $promotion = ProductPromotion::create($data);
        if (isset($data['galleries'])) {
            $promotion->galleries()->sync($data['galleries']);
        }

        return $promotion;
    }
}
