<?php

namespace Feature\API;

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
        $this->seederSupplier();
        $this->headers = array_merge($this->headers, $this->getAuthorizationHeader());
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
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
