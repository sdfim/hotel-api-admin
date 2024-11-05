<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelPromotionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'promotion_name' => 'required|string|max:255',
            'description' => 'required|string',
            'validity_start' => 'required|date',
            'validity_end' => 'required|date',
            'booking_start' => 'required|date',
            'booking_end' => 'required|date',
            'terms_conditions' => 'required|string',
            'exclusions' => 'required|string',
        ];
    }
}
