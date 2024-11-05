<?php

namespace Modules\Insurance\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class InsuaranceAddRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'booking_item' => 'required|size:36|unique:insurance_plans,booking_item',
            'insurance_provider' => 'required|exists:insurance_providers,name',
        ];
    }
}
