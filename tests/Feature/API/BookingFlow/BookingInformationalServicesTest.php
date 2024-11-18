<?php

namespace Tests\Feature\API\BookingFlow;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;

class BookingInformationalServicesTest extends BaseBookingFlowTest
{
    #[Test]
    public function test_search(): void
    {
        parent::test_search();
    }

    #[Test]
    #[Depends('test_search')]
    public function test_add_booking_item(): void
    {
        parent::test_add_booking_item();
    }

    #[Test]
    #[Depends('test_add_booking_item')]
    public function test_add_insurance()
    {
        $request = [
            'booking_item' => self::$bookingItem,
            'services' => [
                [
                    'service_id' => ConfigServiceType::first()->id,
                    'cost' => 100
                ]
            ]
        ];
        $response = $this->request()->post(route('attachService'), $request);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    #[Test]
    #[Depends('test_add_insurance')]
    public function test_delete_insurance()
    {
        $request = [
            'booking_item' => self::$bookingItem,
            'services' => [
                [
                    'service_id' => ConfigServiceType::first()->id,
                    'cost' => 100
                ]
            ]
        ];
        $response = $this->request()->post(route('detachService'), $request);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
