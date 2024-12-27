<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;
use Modules\Enums\AgeRestrictionTypeEnum;

class ProductAgeRestrictionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:pd_products,id',
            'restriction_type' => 'required|in:' . implode(',', array_column(AgeRestrictionTypeEnum::cases(), 'value')),
            'value' => 'required|integer',
            'active' => 'required|boolean',
        ];
    }
}
