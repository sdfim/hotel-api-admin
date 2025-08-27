<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test Channel
        if (! Channel::where('name', 'Test Channel')->first()) {
            $data = [];
            $data['name'] = 'Test Channel';
            $data['description'] = 'For API test requests';
            $channel = Channel::create($data);
            $token = $channel->createToken($data['name']);
            $channel->update([
                'token_id' => $token->accessToken->id,
                'access_token' => $token->plainTextToken,
            ]);
        }

        // UI Channel
        if (! Channel::where('name', 'UI Channel')->first()) {
            $data = [];
            $data['name'] = 'UI Channel';
            $data['description'] = 'For UI requests, e.g. cancel reservations';
            $channel = Channel::create($data);
            $token = $channel->createToken($data['name']);
            $channel->update([
                'token_id' => $token->accessToken->id,
                'access_token' => $token->plainTextToken,
            ]);
        }
    }
}
