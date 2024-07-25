<?php

namespace Tests\Feature\CustomAuthorizedActions;

class ContentControllerTest extends CustomAuthorizedActionsTestCase
{
    /**
     * @test
     */
    public function test_admin_index_is_opening(): void
    {
        $response = $this->get('/admin/content');

        $response->assertStatus(200);
    }
}
