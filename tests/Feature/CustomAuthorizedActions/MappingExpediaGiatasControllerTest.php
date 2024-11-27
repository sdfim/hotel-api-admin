<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Models\Mapping;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class MappingExpediaGiatasControllerTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_index_view_is_rendered_correctly()
    {
        Mapping::factory()->count(5)->create();

        $response = $this->get(route('expedia.index'));

        $response->assertStatus(200);

        $response->assertViewIs('dashboard.expedia.index');
    }
}
