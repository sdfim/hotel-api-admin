<?php

namespace Tests\Feature\CustomAuthorizedActions;

class ContentControllerTestCustom extends CustomAuthorizedActionsTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_admin_index_is_opening(): void
    {
        $response = $this->get('/admin/content');

        $response->assertStatus(200);
    }
}
