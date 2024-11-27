<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\HotelWebFinder;
use Illuminate\Support\Collection;

class HotelWebFinderDTO
{
    public $id;
    public $name;
    public $url;

    public function transform(Collection $hotelWebFinders)
    {
        return $hotelWebFinders->map(function ($webFinder) {
            return $this->transformWebFinder($webFinder);
        })->all();
    }

    private function transformWebFinder(HotelWebFinder $hotelWebFinder)
    {
        return [
            'id' => $hotelWebFinder->id,
            'name' => $hotelWebFinder->name,
            'url' => $hotelWebFinder->url,
        ];
    }
}
