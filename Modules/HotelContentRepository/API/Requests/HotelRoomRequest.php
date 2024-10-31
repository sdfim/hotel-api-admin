<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelRoomRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'hbsi_data_mapped_name' => 'required|string|max:255',
            'description' => 'required|string',
        ];
    }
}
