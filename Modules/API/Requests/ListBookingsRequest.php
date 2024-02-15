<?php

namespace Modules\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class ListBookingsRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }
    public function rules(): array
    {
        return [
            'supplier' => 'required|string',
            'type' => 'required|string|in:hotel,flight,combo'
        ];
    }
}
