<?php

namespace Modules\HotelContentRepository\Http\Requests;

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
            'hotel_id' => 'required|integer',
            'section_name' => 'required|string|max:255',
            'meta_description' => 'required|string',
            'property_description' => 'required|string',
            'cancellation_policy' => 'required|string',
            'pet_policy' => 'required|string',
            'terms_conditions' => 'required|string',
            'fees_paid_at_hotel' => 'required|string',
            'staff_contact_info' => 'required|string',
            'validity_start' => 'required|date',
            'validity_end' => 'nullable|date',
        ];
    }
}
