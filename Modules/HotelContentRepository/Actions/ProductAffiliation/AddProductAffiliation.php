<?php

namespace Modules\HotelContentRepository\Actions\ProductAffiliation;

use Modules\HotelContentRepository\API\Requests\ProductAffiliationRequest;
use Modules\HotelContentRepository\Events\ProductAffiliation\ProductAffiliationAdded;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class AddProductAffiliation
{
    public function handle(ProductAffiliationRequest $request)
    {
        $productAffiliation = ProductAffiliation::create($request->validated());
        ProductAffiliationAdded::dispatch($productAffiliation);

        return $productAffiliation;
    }
}
