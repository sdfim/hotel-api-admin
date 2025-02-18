<?php

namespace Modules\API\Suppliers\Transformers\Expedia;

class ExpediaTranformerService
{
    public function transformToNameValueArray(array $items, array $additions = []): array
    {
        $result = [];
        foreach ($items as $key => $value) {
            $i = [
                'name' => $key,
                'value' => $value,
            ];
            foreach ($additions as $addition) {
                $i[$addition] = null;
            }
            $result[] = $i;
        }

        return $result;
    }
}
