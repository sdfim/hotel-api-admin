<?php

namespace Modules\HotelContentRepository\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelAffiliationRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'affiliation_name' => 'required|string|max:255',
            'combinable' => 'required|boolean',
        ];
    }
}




