<?php

namespace Modules\API\PricingAPI\ResponseModels;

use ReflectionClass;

class BaseResponse
{
    public function validateArrayKeys(array $data): bool
    {
        $reflector = new ReflectionClass($this);
        $properties = $reflector->getProperties();
        $classProperties = array_map(function ($property) {
            return $property->getName();
        }, $properties);
        $arrayKeys = array_keys($data);

        sort($classProperties);
        sort($arrayKeys);

        return empty(array_diff($arrayKeys, $classProperties));
    }
}
