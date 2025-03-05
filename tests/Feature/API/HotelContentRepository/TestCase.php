<?php

namespace Tests\Feature\API\HotelContentRepository;

use App\Models\Channel;
use App\Models\GeneralConfiguration;
use App\Models\User;
use Database\Seeders\ConfigServiceTypeSeeder;
use Database\Seeders\GeneralConfigurationSeeder;
use Database\Seeders\SuppliersSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Insurance\Seeders\InsuranceRateTierSeeder;
use Modules\Insurance\Seeders\InsuranceRestrictionTypeSeeder;
use Modules\Insurance\Seeders\InsuranceTypeSeeder;
use Modules\Insurance\Seeders\InsuranceVendorSeeder;
use Modules\Insurance\Seeders\TripMateDefaultRestrictionsSeeder;
use Tests\RefreshDatabaseMany;

class TestCase extends BaseTestCase
{
    use RefreshDatabaseMany;

    protected static User $user;

    protected static string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    protected function setUpTestData(): void
    {
        if (! isset(self::$user)) {
            $this->runSeeders();
            $this->setAuth();
        }
    }

    protected function runSeeders(): void
    {
        $this->seed(SuppliersSeeder::class);
        $this->seed(InsuranceVendorSeeder::class);
        $this->seed(ConfigServiceTypeSeeder::class);
        $this->seed(InsuranceRestrictionTypeSeeder::class);
        $this->seed(InsuranceTypeSeeder::class);
        $this->seed(TripMateDefaultRestrictionsSeeder::class);
        $this->seed(InsuranceRateTierSeeder::class);
        if (! GeneralConfiguration::exists()) {
            $this->seed(GeneralConfigurationSeeder::class);
        }
    }

    protected function setAuth(?int $tokenId = null): void
    {
        if (isset($tokenId)) {
            $channel = Channel::where('token_id', $tokenId)->firstOrFail();
            self::$accessToken = $channel->access_token;
            self::$user = User::whereHas(
                'tokens',
                fn ($q) => $q->where('personal_access_tokens.id', $tokenId)
            )->firstOrFail();
        } else {
            self::$user = User::factory()->create();
            $token = self::$user->createToken('Test');
            self::$accessToken = $token->plainTextToken;
            Channel::create([
                'token_id' => $token->accessToken->id,
                'access_token' => $token->plainTextToken,
                'name' => 'Test channel',
                'description' => 'Temp channel',
            ]);
        }
    }

    protected function request(): self
    {
        return $this->actingAs(self::$user)
            ->withHeader(
                'Authorization',
                'Bearer '.self::$accessToken
            );
    }
}
