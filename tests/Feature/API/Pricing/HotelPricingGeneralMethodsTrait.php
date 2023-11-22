<?php

namespace Feature\API\Pricing;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

trait HotelPricingGeneralMethodsTrait
{
    use WithFaker;
    /**
     * @return array
     */
    protected function generateHotelPricingSearchRequestData(): array
    {
        $data = [
            'type' => 'hotel',
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'CAD', 'JPY']),
            'supplier' => 'Expedia',
            'hotel_name' => 'Sheraton',
            'checkin' => $currentDate = Carbon::now()->addDays(7)->toDateString(),
            'checkout' => Carbon::parse($currentDate)->addDays(rand(2, 5))->toDateString(),
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'occupancy' => [],
        ];

        $roomCount = rand(1, 4);

        for ($i = 0; $i < $roomCount; $i++) {
            $haveChildren = rand(0, 1);
            $occupancy = [
                'adults' => rand(1, 3),
            ];

            if ($haveChildren) {
                $numberOfChildren = rand(1, 2);
                $childrenAges = [];

                for ($c = 0; $c < $numberOfChildren; $c++) {
                    $childrenAges[] = rand(1, 12);
                }

                $occupancy['children'] = $numberOfChildren;
                $occupancy['children_ages'] = $childrenAges;
            }

            $data['occupancy'][] = $occupancy;
        }

        return $data;
    }
}
