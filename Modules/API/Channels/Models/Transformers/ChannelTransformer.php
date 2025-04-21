<?php

namespace Modules\API\Channels\Models\Transformers;

use App\Models\Channel;
use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductAttribute;

class ChannelTransformer extends TransformerAbstract
{
    public function transform(Channel $model)
    {
        return [
            'name' => $model->name,
            'id'  => $model->id,
            'description'  => $model->description,
            'token_id'  => $model->token_id,
            'access_token'  => $model->access_token,
        ];
    }
}
