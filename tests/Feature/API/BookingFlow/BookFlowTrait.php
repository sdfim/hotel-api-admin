<?php

namespace Tests\Feature\API\BookingFlow;

use Mockery;
use Modules\API\Controllers\ApiHandlers\PricingSuppliers\HbsiHotelController;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Tools\Geography;

trait BookFlowTrait
{
    public function searchMock(): void
    {
        $mock = Mockery::mock(HbsiHotelController::class, [new HbsiClient(), new Geography()])->makePartial();
        $mock->shouldReceive('preSearchData')
            ->andReturn($this->preSearchData());
        $this->app->instance(HbsiHotelController::class, $mock);
    }

    private function preSearchData(): array
    {
        return [
            "data" =>  [
                51721 => [
                    "giata" => 42851280,
                    "name" => "Nizuc Resort And Spa",
                    "hbsi" => "51721"
                ],
                51722 => [
                    "giata" => 18774844,
                    "name" => "Moon Palace Nizuc",
                    "hbsi" => "51722"
                ],
            ],
            "total_pages" => 1.0
        ];
    }
}
