<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\API\Validate\ApiRequest;

class VendorRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|array',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'website' => 'nullable|string|max:255',
        ];
    }
}
