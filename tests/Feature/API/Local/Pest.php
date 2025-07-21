<?php

use App\Models\Channel;
use App\Models\Supplier;
use Tests\TestCase;

uses(TestCase::class)->beforeEach(function () {
    // Logic from ApiTestCase::getAuthorizationHeader()
    $channel = Channel::factory()->create();
    $token = $channel->access_token;
    $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ]);

    // Logic from ApiTestCase::seederSupplier()
    $suppliers = [
        [
            'id' => 1,
            'name' => 'Expedia',
            'description' => 'Expedia Description',
        ],
        [
            'id' => 2,
            'name' => 'HBSI',
            'description' => 'HBSI Description',
        ],
    ];

    foreach ($suppliers as $supplierData) {
        Supplier::firstOrNew($supplierData)->save();
    }
})->in(__DIR__);
