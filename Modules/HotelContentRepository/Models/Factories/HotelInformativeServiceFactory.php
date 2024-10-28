<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelInformativeService;

class HotelInformativeServiceFactory extends Factory
{
    protected $model = HotelInformativeService::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'service_id' => ConfigServiceType::factory(),
        ];
    }
}
