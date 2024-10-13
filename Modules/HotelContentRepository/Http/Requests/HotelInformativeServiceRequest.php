<?php

namespace Modules\HotelContentRepository\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelInformativeServiceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'service_name' => 'required|string|max:255',
            'service_description' => 'required|string',
            'service_cost' => 'required|numeric',
        ];
    }
}
