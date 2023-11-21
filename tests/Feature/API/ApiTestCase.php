<?php

namespace Feature\API;

use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class ApiTestCase extends TestCase
{
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
    protected function getHeader(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
