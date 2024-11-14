<?php

namespace Modules\Insurance\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class InsuranceAddRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        // we can use 'booking_item' => 'required|size:36|unique:insurance_plans,booking_item', if we want to check if
        // InsurancePlan for required booking_item is already created
        return [
            'booking_item' => 'required|size:36|exists:api_booking_items,booking_item',
            'insurance_provider' => 'required|exists:insurance_providers,name',
        ];
    }
}
