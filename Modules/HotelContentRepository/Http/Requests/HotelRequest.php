<?php

namespace Modules\HotelContentRepository\Http\Requests;

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
            'type' => 'required|string|max:255',
            'verified' => 'required|boolean',
            'direct_connection' => 'required|boolean',
            'manual_contract' => 'required|boolean',
            'commission_tracking' => 'required|boolean',
            'address' => 'required|string',
            'star_rating' => 'required|integer|min:1|max:5',
            'website' => 'required|string|max:255',
            'num_rooms' => 'required|integer',
            'featured' => 'required|boolean',
            'location' => 'required|string|max:255',
            'content_source' => 'required|in:IcePortal,Expedia,Internal',
            'room_images_source' => 'required|in:IcePortal,Expedia,Internal',
            'property_images_source' => 'required|in:IcePortal,Expedia,Internal',
            'channel_management' => 'required|boolean',
            'hotel_board_basis' => 'required|string|max:255',
            'default_currency' => 'required|string|max:10',
        ];
    }
}
