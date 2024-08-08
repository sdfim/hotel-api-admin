<?php

namespace App\Repositories;

use App\Models\PropertyWeighting;
use Illuminate\Database\Eloquent\Collection;

class PropertyWeightingRepository
{
    public function getWeights(): PropertyWeighting|Collection
    {
        return PropertyWeighting::where('supplier_id', null)->get();
    }

    public function getWeightsNot(): PropertyWeighting|Collection
    {
        return PropertyWeighting::whereNot('supplier_id', null)->get();
    }
}
