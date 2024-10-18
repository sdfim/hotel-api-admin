<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelDescriptiveContentSectionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|exists:pd_hotels,id',
            'section_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ];
    }
}
