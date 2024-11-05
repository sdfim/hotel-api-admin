<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelAgeRestrictionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|exists:pd_hotels,id',
            'restriction_type_id' => 'required|exists:pd_hotel_age_restriction_types,id',
            'value' => 'required|integer',
            'active' => 'required|boolean',
        ];
    }
}
