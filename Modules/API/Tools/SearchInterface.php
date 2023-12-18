<?php

namespace Modules\API\Tools;

interface SearchInterface
{
    public function search(string $name, float $latitude): array;

}
