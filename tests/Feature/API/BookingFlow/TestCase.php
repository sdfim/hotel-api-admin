<?php

namespace Tests\Feature\API\BookingFlow;

use App\Models\Channel;
use App\Models\GeneralConfiguration;
use App\Models\User;
use Database\Seeders\GeneralConfigurationSeeder;
use Database\Seeders\SuppliersSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected static User $user;
    protected static string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    protected function setUpTestData(): void
    {
        if (!isset(self::$user)) {
            $this->runSeeders();
            $this->setAuth();
        }
    }

    protected function runSeeders(): void
    {
        $this->seed(SuppliersSeeder::class);
        if (!GeneralConfiguration::exists()) {
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
                fn($q) => $q->where('personal_access_tokens.id', $tokenId)
            )->firstOrFail();
        } else {
            self::$user = User::factory()->create();
            $token = self::$user->createToken('Test');
            self::$accessToken = $token->plainTextToken;
            Channel::create([
                'token_id'     => $token->accessToken->id,
                'access_token' => $token->plainTextToken,
                'name'         => 'Test channel',
                'description'  => 'Temp channel',
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
