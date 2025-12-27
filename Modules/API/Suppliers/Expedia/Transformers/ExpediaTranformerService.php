<?php

namespace Modules\API\Suppliers\Expedia\Transformers;

class ExpediaTranformerService
{
    public function transformToNameValueArray(mixed $items, array $additions, ?string $name = null): array
    {
        $result = [];
        if (! is_array($items)) {
            $items = [];
        }
        foreach ($items ?? [] as $key => $value) {
            $i = [
                'name' => $name ?? $key,
                'value' => $value,
            ];
            foreach ($additions as $addition) {
                $i[$addition] = null;
            }
            $result[] = $i;
        }

        return $result;
    }

    public function parseAttractions(string $attractions): array
    {
        $pattern = '/<br \/> ([^<]+) - ([0-9.]+) km \/ ([0-9.]+) mi/';
        preg_match_all($pattern, $attractions, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $match) {
            $result[] = [
                'name' => trim($match[1]),
                'distance_km' => (float) $match[2],
                'distance_mi' => (float) $match[3],
            ];
        }

        return $result;
    }
}
