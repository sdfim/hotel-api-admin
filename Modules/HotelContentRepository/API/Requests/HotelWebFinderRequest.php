<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelWebFinderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hotel_id' => 'required|integer',
            'base_url' => 'required|string|max:255',
            'finder' => 'required|string|max:255',
        ];
    }
}
