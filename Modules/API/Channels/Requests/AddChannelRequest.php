<?php

namespace Modules\API\Channels\Requests;

use Modules\API\Validate\ApiRequest;

class AddChannelRequest  extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:190',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
