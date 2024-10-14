<?php

namespace Modules\HotelContentRepository\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelImageRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'image_url' => 'required|string|max:255',
            'tag' => 'required|string|max:100',
            'weight' => 'required|integer',
            'section' => 'required|in:gallery,hotel,room,promotion,exterior,amenities',
        ];
    }
}
