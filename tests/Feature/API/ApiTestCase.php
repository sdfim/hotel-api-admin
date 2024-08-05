<?php

namespace Tests\Feature\API;

use App\Models\Channel;
use App\Models\Supplier;
use Tests\TestCase;

class ApiTestCase extends TestCase
{
    /**
     * @var array|string[]
     */
    protected array $headers = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth();

        $this->headers = array_merge($this->headers, $this->getAuthorizationHeader());

        $this->seederSupplier();
    }

    protected function seederSupplier(): void
    {
        $suppliers = [
            [
                'id' => 1,
                'name' => 'Expedia',
                'description' => 'Expedia Description'
            ],
            [
                'id' => 2,
                'name' => 'HBSI',
                'description' => 'HBSI Description'
            ]
        ];

        foreach ($suppliers as $supplierData) {
            $supplier = Supplier::firstOrNew($supplierData);
            $supplier->save();
        }
    }

    /**
     * @return string[]
     */
    protected function getAuthorizationHeader(): array
    {
        $channel = Channel::factory()->create();

        $token = $channel->access_token;

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }
}
