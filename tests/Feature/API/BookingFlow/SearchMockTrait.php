<?php

namespace Tests\Feature\API\BookingFlow;

use Mockery;
use Modules\API\Controllers\ApiHandlers\HotelSuppliers\HbsiHotelController;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Tools\Geography;
use Modules\API\Tools\PricingDtoTools;

trait SearchMockTrait
{
    public function searchMock(): void
    {
        $mock = Mockery::mock(HbsiHotelController::class, [new HbsiClient, new Geography])->makePartial();
        $mock->shouldReceive('preSearchData')
            ->andReturn($this->preSearchData());
        $this->app->instance(HbsiHotelController::class, $mock);

        $mock = Mockery::mock(PricingDtoTools::class)->makePartial();
        $mock->shouldReceive('getDestinationData')
            ->andReturn('Cancun, Yucatán Peninsula, Mexico');
        $mock->shouldReceive('getGiataProperties')
            ->andReturn([
                18774844 => ['city' => 'Cancun'],
                42851280 => ['city' => 'Cancun'],
            ]);
        $this->app->instance(PricingDtoTools::class, $mock);
    }

    private function preSearchData(): array
    {
        return [
            'data' => [
                85000 => [
                    'giata' => 10057691,
                    'name' => 'Garza Blanca Resort and Spa',
                    'hbsi' => '85000',
                ],
                72576 => [
                    'giata' => 12528742,
                    'name' => 'Le Blanc Spa Resort',
                    'hbsi' => '72576',
                ],
                51722 => [
                    'giata' => 18774844,
                    'name' => 'Moon Palace Nizuc',
                    'hbsi' => '51722',
                ],
                51721 => [
                    'giata' => 42851280,
                    'name' => 'Nizuc Resort And Spa',
                    'hbsi' => '51721',
                ],
                77957 => [
                    'giata' => 42851280,
                    'name' => 'Nizuc Resort And Spa',
                    'hbsi' => '77957',
                ],
                70841 => [
                    'giata' => 72765633,
                    'name' => 'Hard Rock Hotel Cancun',
                    'hbsi' => '70841',
                ],
                81234 => [
                    'giata' => 89050775,
                    'name' => 'Dreams Natura Resort & Spa',
                    'hbsi' => '81234',
                ],
                60171 => [
                    'giata' => 93312535,
                    'name' => 'Paradisus Cancún',
                    'hbsi' => '60171',
                ],
            ],
            'total_pages' => 1.0,
        ];
    }
}
