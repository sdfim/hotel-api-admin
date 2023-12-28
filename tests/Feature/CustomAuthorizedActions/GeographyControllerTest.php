<?php

namespace Tests\Feature\CustomAuthorizedActions;

class GeographyControllerTest extends CustomAuthorizedActionsTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_admin_geography_index_is_opening(): void
    {
        $response = $this->get('/admin/geography');

        $response->assertStatus(200);
    }
}
