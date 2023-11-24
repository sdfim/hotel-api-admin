<?php

namespace Tests\Feature\API;

use App\Models\Channel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array|string[]
     */
    protected array $headers;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->auth();
        $this->headers = array_merge($this->headers, $this->getAuthorizationHeader());
        $this->seederSupplier();
    }

    /**
     * @return void
     */
    protected function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }

    /**
     * @return void
     */
    protected function seederSupplier(): void
    {
        $supplier = Supplier::firstOrNew([
            'name' => 'Expedia',
            'description' => 'Expedia Description']);
        $supplier->save();
    }

    /**
     * @return string[]
     */
    protected function getAuthorizationHeader(): array
    {
        $channel = Channel::factory()->create();
        $token = $channel->access_token;
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
