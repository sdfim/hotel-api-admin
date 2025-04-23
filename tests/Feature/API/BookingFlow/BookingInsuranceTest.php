<?php

namespace Tests\Feature\API\BookingFlow;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;

class BookingInsuranceTest extends BaseBookingFlow
{
    #[Test]
    public function test_search(): void
    {
        self::$stage = 0;
        parent::search();
    }

    #[Test]
    #[Depends('test_search')]
    public function test_add_booking_item(): void
    {
        parent::add_booking_item();
    }

    #[Test]
    #[Depends('test_add_booking_item')]
    public function test_add_passengers(): void
    {
        parent::add_passengers();
    }

    #[Test]
    #[Depends('test_add_passengers')]
    public function test_add_insurance()
    {
        $request = [
            'booking_item' => self::$bookingItem,
            'vendor' => 'TripMate'
        ];
        $response = $this->request()->post(route('addInsurance'), $request);

        $response->assertStatus(201);
    }

    #[Test]
    #[Depends('test_add_insurance')]
    public function test_delete_insurance()
    {
        $request = [
            'booking_item' => self::$bookingItem,
            'vendor' => 'TripMate'
        ];

        $response = $this->request()->delete(route('deleteInsurance'), $request);

        $response->assertStatus(204);
    }

    #[Test]
    #[Depends('test_add_booking_item')]
    public function test_search_again(): void
    {
        self::$stage = 0;
        parent::search();
    }

    #[Test]
    #[Depends('test_search_again')]
    public function test_add_booking_item_again(): void
    {
        parent::add_booking_item();
    }

    #[Test]
    #[Depends('test_search_again')]
    public function test_add_insurance_by_booking_id()
    {
        $request = [
            'booking_id' => self::$bookingId,
            'vendor' => 'TripMate'
        ];
        $response = $this->request()->post(route('addInsurance'), $request);

        $response->assertStatus(201);
    }

    #[Test]
    #[Depends('test_add_insurance_by_booking_id')]
    public function test_delete_insurance_by_booking_id()
    {
        $request = [
            'booking_id' => self::$bookingId,
            'vendor' => 'TripMate'
        ];

        $response = $this->request()->delete(route('deleteInsurance'), $request);

        $response->assertStatus(204);
    }
}
