<?php

namespace App\Services;

interface SupplierInterface
{
    public function getResults(array $giataCodes): array;
}
