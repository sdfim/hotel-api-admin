<?php

namespace Database\Factories;

use App\Models\Channels;
use App\Models\ExpediaContent;
use App\Models\Supplier;
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
            'property_id' => $this->faker->numberBetween(1, 10000), // Пример значения для property_id
            'rating' => $this->faker->randomFloat(2, 1, 5), // Пример значения для rating
            'name' => $this->faker->name,
            'giata_TTIcode' => $this->faker->numberBetween(1, 10000),
            'city' => $this->faker->city,
            'state_province_code' => $this->faker->stateAbbr,
            'state_province_name' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country_code' => $this->faker->countryCode,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'category_name' => $this->faker->word,
            'checkin_time' => $this->faker->time,
            'checkout_time' => $this->faker->time,
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
            'phone' => $this->faker->phoneNumber,
            'fax' => $this->faker->phoneNumber,
            'tax_id' => $this->faker->isbn10,
            'category' => '{
                "id": "1",
                "name": "Hotel"
            }',
            'business_model' => '{
                "expedia_collect": true,
                "property_collect": true
            }',
            'rank' => $this->faker->word,
            'checkin' => '{
                "min_age": 21,
                "end_time": "anytime",
                "begin_time": "3:00 PM",
                "instructions": "<ul>  <li>Extra-person charges may apply and vary depending on property policy</li><li>Government-issued photo identification and a credit card, debit card, or cash deposit may be required at check-in for incidental charges</li><li>Special requests are subject to availability upon check-in and may incur additional charges; special requests cannot be guaranteed</li><li>This property accepts credit cards, debit cards, and cash</li><li>Safety features at this property include a fire extinguisher, a smoke detector, a security system, a first aid kit, and window guards</li><li>Please note that cultural norms and guest policies may differ by country and by property; the policies listed are provided by the property</li>  </ul> <ul>  <li>Guests are permitted to bring maximum 1-liter bottle of alcohol per adult for in-room consumption.</li><li>Guests are not permitted to use external speakers on site.</li><li>No visitors or unregistered guests are allowed in guestrooms</li><li>Loud music is not permitted in guestrooms.</li>  </ul>",
                "special_instructions": "This property offers transfers from the airport (surcharges may apply). Guests must contact the property with arrival details before travel, using the contact information on the booking confirmation. Front desk staff will greet guests on arrival. For more details, please contact the property using the information on the booking confirmation.  Check-in hours are 3 PM on Saturday and Sunday."
            }',
            'checkout' => '{}',
            'fees' => '{}',
            'policies' => '{}',
            'attributes' => '{}',
            'amenities' => '{}',
            'images' => '{}',
            'onsite_payments' => '{}',
            'rooms' => '{}',
            'total_occupancy' => $this->faker->word,
            'rooms_occupancy' => '{}',
            'rates' => '{}',
            'dates' => '{}',
            'descriptions' => '{}',
            'themes' => '{}',
            'chain' => '{}',
            'brand' => '{}',
            'statistics' => '{}',
            'multi_unit' => $this->faker->boolean,
            'payment_registration_recommended' => $this->faker->boolean,
            'vacation_rental_details' => '{}',
            'airports' => '{}',
            'spoken_languages' => '{
                "da": {
                    "id": "da",
                    "name": "Danish"
                },
                "en": {
                    "id": "en",
                    "name": "English"
                },
                "fr": {
                    "id": "fr",
                    "name": "French"
                },
                "hi": {
                    "id": "hi",
                    "name": "Hindi"
                }
            }',
            'supply_source' => $this->faker->word,
            'all_inclusive' => [""],

        ];
    }
}
