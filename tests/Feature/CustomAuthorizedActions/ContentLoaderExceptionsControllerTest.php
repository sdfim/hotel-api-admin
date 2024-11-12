<?php

namespace Tests\Feature\CustomAuthorizedActions;

use PHPUnit\Framework\Attributes\Test;

class ContentLoaderExceptionsControllerTest extends CustomAuthorizedActionsTestCase
{
    #[Test]
    public function test_content_loader_exceptions_is_opening(): void
    {
        $response = $this->get('/admin/exceptions-report');

        $response->assertStatus(200);
    }
}
