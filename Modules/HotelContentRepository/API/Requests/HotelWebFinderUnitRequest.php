<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelWebFinderUnitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'web_finder_id' => 'required|integer',
            'field' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ];
    }
}
