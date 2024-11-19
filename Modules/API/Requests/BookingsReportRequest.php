<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingsReportRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from'  => 'nullable|date_format:Y-m-d',
            'to'    => 'nullable|date_format:Y-m-d',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
