<?php

namespace Modules\HotelContentRepository\Actions\ProductAffiliation;

use Modules\HotelContentRepository\API\Requests\ProductAffiliationRequest;
use Modules\HotelContentRepository\Events\ProductAffiliation\ProductAffiliationEdited;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class EditProductAffiliation
{
    public function handle(ProductAffiliation $productAffiliation, ProductAffiliationRequest $request)
    {
        $productAffiliation->update($request->validated());
        ProductAffiliationEdited::dispatch($productAffiliation);
        return $productAffiliation;
    }
}
