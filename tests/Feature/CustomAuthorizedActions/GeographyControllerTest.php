<?php

namespace Tests\Feature\CustomAuthorizedActions;

use PHPUnit\Framework\Attributes\Test;

class GeographyControllerTest extends CustomAuthorizedActionsTestCase
{
    #[Test]
    public function test_admin_geography_index_is_opening(): void
    {
        $response = $this->get('/admin/geography');

        $response->assertStatus(200);
    }
}
