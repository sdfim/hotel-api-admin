<?php

namespace Tests\Feature\CustomAuthorizedActions;

class ExceptionReportsTest extends CustomAuthorizedActionsTestCase
{
    /**
     * @test
     */
    public function test_example(): void
    {
        $response = $this->get('/admin/exceptions-report');

        $response->assertStatus(200);
    }
}
