<?php

namespace Modules\HotelContentRepository\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class KeyMappingRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'key_id' => 'required|string|max:255',
            'key_mapping_owner_id' => 'required|exists:pd_key_mapping_owners,id',
            ];
    }
}
