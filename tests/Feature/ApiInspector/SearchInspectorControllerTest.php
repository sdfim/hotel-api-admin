<?php

namespace Tests\Feature\ApiInspector;

use App\Models\ApiSearchInspector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class SearchInspectorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_search_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/search-inspector');

        $response->assertStatus(200);
    }
}
