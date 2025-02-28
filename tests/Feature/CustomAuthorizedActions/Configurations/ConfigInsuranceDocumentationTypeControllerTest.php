<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Models\Configurations\ConfigInsuranceDocumentationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigInsuranceDocumentationTypeControllerTest extends CustomAuthorizedActionsTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_the_index_view()
    {
        $response = $this->get(route('configurations.insurance-documentation-types.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.configurations.insurance_documentation_types.index');
    }

    #[Test]
    public function it_displays_the_create_view()
    {
        $response = $this->get(route('configurations.insurance-documentation-types.create'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.configurations.insurance_documentation_types.form');
        $response->assertViewHas('configInsuranceDocumentationType');
        $response->assertViewHas('text');
    }
}
