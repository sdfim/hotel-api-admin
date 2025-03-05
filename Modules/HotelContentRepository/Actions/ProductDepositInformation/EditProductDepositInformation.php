<?php

namespace Modules\HotelContentRepository\Actions\ProductDepositInformation;

use Modules\HotelContentRepository\API\Requests\ProductDepositInformationRequest;
use Modules\HotelContentRepository\Events\ProductDepositInformation\ProductDepositInformationEdited;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class EditProductDepositInformation
{
    public function handle(ProductDepositInformation $productDepositInformation, ProductDepositInformationRequest $request)
    {
        $productDepositInformation->update($request->validated());
        ProductDepositInformationEdited::dispatch($productDepositInformation);
        return $productDepositInformation;
    }

    public function updateWithConditions(ProductDepositInformation $productDepositInformation, array $data): void
    {
        $productDepositInformation->update($data);

        if (isset($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                    $condition['value_from'] = null;
                } else {
                    $condition['value'] = null;
                }
                if (isset($condition['id'])) {
                    $productDepositInformation->conditions()->updateOrCreate(['id' => $condition['id']], $condition);
                } else {
                    $productDepositInformation->conditions()->create($condition);
                }
            }
        }
    }
}
