<?php

namespace Tests\Feature\CustomAuthorizedActions;

class ContentLoaderExceptionsControllerTestCustom extends CustomAuthorizedActionsTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_content_loader_exceptions_is_opening(): void
    {
        $response = $this->get('/admin/exceptions-report');

        $response->assertStatus(200);
    }
}
