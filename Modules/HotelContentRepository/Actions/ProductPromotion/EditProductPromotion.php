<?php

namespace Modules\HotelContentRepository\Actions\ProductPromotion;

use Modules\HotelContentRepository\Models\ProductPromotion;

class EditProductPromotion
{
    public function updateWithGalleries(ProductPromotion $record, array $data): void
    {
        $record->update($data);
        if (isset($data['galleries'])) {
            $record->galleries()->sync($data['galleries']);
        }
    }
}
