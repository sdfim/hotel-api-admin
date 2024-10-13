<?php

namespace Modules\HotelContentRepository\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class TravelAgencyCommissionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'consortium_id' => 'required|integer',
            'room_type' => 'required|string|max:255',
            'commission_value' => 'required|numeric',
            'date_range_start' => 'required|date',
            'date_range_end' => 'required|date',
        ];
    }
}
