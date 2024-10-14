<?php

namespace Tests\Feature\ApiInspector;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class BookingItemsTableTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_booking_items_table_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/booking-items');

        $response->assertStatus(200);
    }
}
