<?php

namespace Tests\Feature\API\Booking;

use App\Models\Channel;
use App\Models\User;
use Database\Seeders\GeneralConfigurationSeeder;
use Database\Seeders\SuppliersSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery;
use Modules\API\Controllers\ApiHandlers\PricingSuppliers\HbsiHotelController;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Tools\Geography;
use Modules\API\Tools\PricingDtoTools;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use WithFaker;

    private User $user;
    private string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        (new SuppliersSeeder())->run();
        (new GeneralConfigurationSeeder())->run();

        $token = $this->user->createToken('Test');
        $this->accessToken = $token->plainTextToken;
        Channel::create([
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
            'name' => $this->faker->name(),
            'description' => $this->faker->name(),
        ]);
    }

    /**
     * A basic feature test example.
     */
    public function test_search(): void
    {
        $mock = Mockery::mock(HbsiHotelController::class, [new HbsiClient(), new Geography()])->makePartial();
        $mock->shouldReceive('preSearchData')
            ->andReturn($this->preSearchData());
        $this->app->instance(HbsiHotelController::class, $mock);

        $mock = Mockery::mock(PricingDtoTools::class)->makePartial();
        $mock->shouldReceive('getDestinationData')
            ->andReturn('Cancun, Yucatán Peninsula, Mexico');
        $mock->shouldReceive('getGiataProperties')
            ->andReturn([
                18774844 => ['city' => 'Cancun'],
                42851280 => ['city' => 'Cancun'],
            ]);
        $this->app->instance(PricingDtoTools::class, $mock);

        $checkin = Carbon::now()->addDays(150)->toDateString();
        $checkout = Carbon::now()->addDays(150 + rand(2, 5))->toDateString();

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', 'Bearer '.$this->accessToken)
            ->json('POST', route('price'), [
                'type' => 'hotel',
                'destination' => 508,
                'supplier' => 'HBSI',
                'checkin' => $checkin,
                'checkout' => $checkout,
                'occupancy' => [['adults' => 1]],
            ]);

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data')->has('success')->has('message')
        );
    }

    private function preSearchData(): array
    {
        return [
            "data" =>  [
                85000 => [
                    "giata" => 10057691,
                    "name" => "Garza Blanca Resort and Spa",
                    "hbsi" => "85000",
                ],
                72576 => [
                    "giata" => 12528742,
                    "name" => "Le Blanc Spa Resort",
                    "hbsi" => "72576"
                ],
                51722 => [
                    "giata" => 18774844,
                    "name" => "Moon Palace Nizuc",
                    "hbsi" => "51722"
                ],
                51721 => [
                    "giata" => 42851280,
                    "name" => "Nizuc Resort And Spa",
                    "hbsi" => "51721"
                ],
                77957 => [
                    "giata" => 42851280,
                    "name" => "Nizuc Resort And Spa",
                    "hbsi" => "77957"
                ],
                70841 => [
                    "giata" => 72765633,
                    "name" => "Hard Rock Hotel Cancun",
                    "hbsi" => "70841"
                ],
                81234 => [
                    "giata" => 89050775,
                    "name" => "Dreams Natura Resort & Spa",
                    "hbsi" => "81234"
                ],
                60171 => [
                    "giata" => 93312535,
                    "name" => "Paradisus Cancún",
                    "hbsi" => "60171"
                ]
            ],
            "total_pages" => 1.0
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
