<?php

namespace Modules\Insurance\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class InsuranceDeleteRequest extends ApiRequest
{    public function rules(): array
    {
        return [
            'booking_item' => 'required|size:36',
            'vendor' => 'required|exists:pd_vendors,name',
        ];
    }
}
