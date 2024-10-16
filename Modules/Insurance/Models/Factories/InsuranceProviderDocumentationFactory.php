<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceProviderDocumentation;

class InsuranceProviderDocumentationFactory extends Factory
{
    protected $model = InsuranceProviderDocumentation::class;

    public function definition(): array
    {
        return [
            'provider_id' => InsuranceProvider::factory(),
            'type_document' => $this->faker->word,
            'uri' => $this->faker->url,
        ];
    }
}
