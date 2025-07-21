<?php

namespace Tests\Feature\API\BookingFlow;

use App\Models\Channel;
use App\Models\GeneralConfiguration;
use App\Models\User;
use Database\Seeders\GeneralConfigurationSeeder;
use Database\Seeders\SuppliersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class, WithFaker::class)
    ->beforeEach(function () {
        // Logic from TestCase::setUpTestData and runSeeders
        Artisan::call('db:seed', ['--class' => SuppliersSeeder::class]);
        if (! GeneralConfiguration::exists()) {
            Artisan::call('db:seed', ['--class' => GeneralConfigurationSeeder::class]);
        }

        // Logic from TestCase::setAuth
        $this->user = User::factory()->create();
        $token = $this->user->createToken('Test');
        $this->accessToken = $token->plainTextToken;
        Channel::create([
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
            'name' => 'Test channel',
            'description' => 'Temp channel',
        ]);

        // Make request() helper available
        $this->request = function () {
            return $this->actingAs($this->user)
                ->withHeader(
                    'Authorization',
                    'Bearer '.$this->accessToken
                );
        };

        // Initialize static properties from BaseBookingFlow
        $this->searchId = null;
        $this->bookingItem = null;
        $this->bookingId = null;
        $this->passengersAdded = false;
        $this->stage = 2;
        $this->roomCombinations = [];
    })
    ->in(__DIR__);
