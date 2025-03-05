<?php

namespace Modules\HotelContentRepository\Models\Traits;

trait Filterable
{
    public static function getFilterableFields()
    {
        return (new static)->fillable;
    }

    public function scopeFilter($query)
    {
        $filterableFields = self::getFilterableFields();

        foreach ($filterableFields as $field) {
            if (request()->has($field)) {
                $query->where($field, 'like', '%'.request()->input($field).'%');
            }
        }

        return $query;
    }
}
