<?php

namespace Modules\Insurance\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class InsuranceDeleteRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'booking_item' => 'required|size:36',
            'insurance_provider' => 'required|exists:insurance_providers,name',
        ];
    }
}
