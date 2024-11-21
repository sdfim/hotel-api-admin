<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\HotelWebFinder;

class HotelWebFinderDTO
{
    public $id;
    public $base_url;
    public $finder;
    public $type;
    public $example;
    public $hotels;
    public $units;

    public function __construct(HotelWebFinder $hotelWebFinder)
    {
        $this->id = $hotelWebFinder->id;
        $this->base_url = $hotelWebFinder->base_url;
        $this->finder = $hotelWebFinder->finder;
        $this->type = $hotelWebFinder->type;
        $this->example = $hotelWebFinder->example;
        $this->hotels = $hotelWebFinder->hotels->map(function ($hotel) {
            return new HotelDTO($hotel);
        });
        $this->units = $hotelWebFinder->units->map(function ($unit) {
            return new HotelWebFinderUnitDTO($unit);
        });
    }
}
