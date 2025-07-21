<?php

namespace Modules\HotelContentRepository\Actions\ProductInformativeService;

use Modules\HotelContentRepository\Models\ProductInformativeService;

class EditProductInformativeService
{
    public function updateWithDynamicColumns(ProductInformativeService $record, array $data): void
    {
        $record->update($data);
        $record->dynamicColumns()->delete();
        $record->dynamicColumns()->createMany($data['dynamicColumns']);
    }
}
