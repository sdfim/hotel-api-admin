<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\Serializer\ArraySerializer;

class CustomFractalSerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data): array
    {
        return $data;
    }
}
