<?php

namespace Modules\HotelContentRepository\API\Requests;

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
            'section_id' => 'required|exists:pd_hotel_image_sections,id',
            ];
    }
}
