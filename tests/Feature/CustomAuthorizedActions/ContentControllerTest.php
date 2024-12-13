<?php

namespace Tests\Feature\CustomAuthorizedActions;

use PHPUnit\Framework\Attributes\Test;

class ContentControllerTest extends CustomAuthorizedActionsTestCase
{
    #[Test]
    public function test_admin_index_is_opening(): void
    {
        $response = $this->get('/admin/content');

        $response->assertStatus(200);
    }
}
