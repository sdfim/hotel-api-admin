<?php

namespace Modules\API\Requests;

class ListBookingsRequest
{
    public function rules(): array
    {
        return [
            'supplier' => 'required|string',
            'type' => 'required|string|in:hotel,flight,combo'
        ];
    }
}
