<?php

namespace Modules\Insurance\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
            'booking_item' => 'required|size:36',
            'insurance_provider' => 'required|exists:insurance_providers,name',
        ];
    }
}
