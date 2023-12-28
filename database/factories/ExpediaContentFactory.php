<?php

namespace Database\Factories;

use App\Models\ExpediaContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpediaContent>
 */
class ExpediaContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExpediaContent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => $this->faker->unique()->numberBetween(1, 100000),
            'rating' => $this->faker->randomFloat(2, 1, 5),
            'name' => $this->faker->name,
            'city' => $this->faker->city,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'phone' => $this->faker->phoneNumber,
            'total_occupancy' => $this->faker->word,
            'is_active' => rand(0, 1),
            'address' => '{
                "city": "New Delhi",
                "line_1": "Plot No. ' . $this->faker->numberBetween(1, 100) . ', GMR' . $this->faker->word . '",
                "line_2": "Indira Gandhi International Airport",
                "localized": {
                    "links": {
                        "en-US": {
                            "href": "https://api.ean.com/v3/properties/content?language=en-US&property_id=10210101&include=address&supply_source=expedia",
                            "method": "GET"
                        }
                    }
                },
                "postal_code": "110037",
                "country_code": "IN",
                "state_province_name": "Delhi N.C.R",
                "obfuscation_required": false
            }',
            'ratings' => '{
                "guest": {
                    "count": 456,
                    "comfort": "4.3",
                    "overall": "4.1",
                    "service": "4.2",
                    "location": "5.0",
                    "amenities": "4.1",
                    "condition": "4.3",
                    "cleanliness": "4.3",
                    "neighborhood": "4.0",
                    "recommendation_percent": "89.8"
                },
                "property": {
                    "type": "Star",
                    "rating": "5.0"
                }
            }',
            'location' => '{
                "coordinates": {
                    "latitude": 28.551256,
                    "longitude": 77.120706
                },
                "obfuscation_required": false
            }',
        ];
    }
}
