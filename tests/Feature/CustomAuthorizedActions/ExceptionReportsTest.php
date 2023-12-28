<?php

namespace Tests\Feature\CustomAuthorizedActions;

class ExceptionReportsTest extends CustomAuthorizedActionsTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_example(): void
    {
        $response = $this->get('/admin/exceptions-report');

        $response->assertStatus(200);
    }
}
