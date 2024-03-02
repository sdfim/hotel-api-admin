<?php

namespace App\Repositories;

use App\Models\PropertyWeighting;
use Illuminate\Database\Eloquent\Collection;

class PropertyWeightingRepository
{
    /**
     * @return PropertyWeighting|Collection
     */
    public static function getWeights(): PropertyWeighting|Collection
    {
        return PropertyWeighting::where('supplier_id', null)->get();
    }

    /**
     * @return PropertyWeighting|Collection
     */
    public static function getWeightsNot(): PropertyWeighting|Collection
    {
        return PropertyWeighting::whereNot('supplier_id', null)->get();
    }
}
