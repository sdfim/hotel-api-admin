<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelDescriptiveContentRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'content_sections_id' => 'required|integer',
            'descriptive_type_id' => 'required|integer',
            'value' => 'required|string',
        ];
    }
}
