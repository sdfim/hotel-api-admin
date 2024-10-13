<?php

namespace Modules\HotelContentRepository\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelFeeTaxRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'net_value' => 'required|numeric',
            'rack_value' => 'required|numeric',
            'tax' => 'required|numeric',
            'type' => 'required|in:per_person,per_night,per_person_per_night',
        ];
    }
}
