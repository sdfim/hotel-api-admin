<?php

namespace Tests\Feature\ApiInspector;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class BookingInspectorTableTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_booking_inspector_table_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/booking-inspector');

        $response->assertStatus(200);
    }
}
