<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class TravelAgencyCommissionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'commission_value' => 'required|numeric',
            'date_range_start' => 'required|date',
            'date_range_end' => 'required|date',
        ];
    }
}
