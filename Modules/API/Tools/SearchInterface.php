<?php

namespace Modules\API\Tools;

interface SearchInterface
{
    /**
     * @param string $name
     * @param float $latitude
     * @param string $city
     * @return array
     */
    public function search(string $name, float $latitude, string $city): array;
}
