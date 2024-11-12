<?php

namespace Tests\Feature\CustomAuthorizedActions;

use PHPUnit\Framework\Attributes\Test;

class ExceptionReportsTest extends CustomAuthorizedActionsTestCase
{
    #[Test]
    public function test_example(): void
    {
        $response = $this->get('/admin/exceptions-report');

        $response->assertStatus(200);
    }
}
