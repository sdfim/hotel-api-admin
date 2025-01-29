<?php

namespace Modules\HotelContentRepository\Actions\ProductDepositInformation;

use Modules\HotelContentRepository\API\Requests\ProductDepositInformationRequest;
use Modules\HotelContentRepository\Events\ProductDepositInformation\ProductDepositInformationAdded;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class AddProductDepositInformation
{
    public function handle(ProductDepositInformationRequest $request)
    {
        $productDepositInformation = ProductDepositInformation::create($request->validated());
        ProductDepositInformationAdded::dispatch($productDepositInformation);
        return $productDepositInformation;
    }

    public function createWithConditions(array $data): ProductDepositInformation
    {
        $productDepositInformation = ProductDepositInformation::create($data);

        if (isset($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                    $condition['value_from'] = null;
                } else {
                    $condition['value'] = null;
                }
                $productDepositInformation->conditions()->create($condition);
            }
        }

        return $productDepositInformation;
    }
}
