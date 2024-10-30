<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Direct connection,Manual contract,Commission tracking',
            'verified' => 'required|boolean',
            'address' => 'required|string',
            'star_rating' => 'required|integer|min:1|max:5',
            'website' => 'required|string|max:255',
            'num_rooms' => 'required|integer',
            'location' => 'required|string|max:255',
            'content_source_id' => 'required|exists:pd_content_sources,id',
            'room_images_source_id' => 'required|exists:pd_content_sources,id',
            'property_images_source_id' => 'required|exists:pd_content_sources,id',
            'channel_management' => 'required|boolean',
            'hotel_board_basis' => 'required|string|max:255',
            'default_currency' => 'required|string|max:10',
        ];
    }
}
