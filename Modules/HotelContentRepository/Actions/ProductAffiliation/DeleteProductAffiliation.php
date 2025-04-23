<?php

namespace Modules\HotelContentRepository\Actions\ProductAffiliation;

use Modules\HotelContentRepository\Events\ProductAffiliation\ProductAffiliationDeleted;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class DeleteProductAffiliation
{
    public function handle(ProductAffiliation $productAffiliation)
    {
        $productAffiliation->delete();
        ProductAffiliationDeleted::dispatch($productAffiliation);
    }
}
