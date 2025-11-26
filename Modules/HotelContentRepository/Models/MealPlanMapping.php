<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MealPlanMapping extends Model
{
    protected $table = 'pd_meal_plan_mappings';

    protected $fillable = [
        'giata_id',
        'rate_plan_code_from_supplier',
        'meal_plan_code_from_supplier',
        'our_meal_plan',
        'is_enabled',
    ];

    /**
     * Scope mappings by GIATA id.
     */
    public function scopeForGiata(Builder $query, int $giataId): Builder
    {
        return $query->where('giata_id', $giataId);
    }

    /**
     * Scope only enabled mappings.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Optional relation to Hotel if you want it.
     * Note: giata_id stores GIATA code, not primary key "id".
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'giata_id', 'giata_code');
    }
}
