<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Channel::first()) {
            $data = [];
            $data['name'] = 'Test Channel';
            $data['description'] = 'Test Channel Description';

            // Create the channel
            $channel = Channel::create($data);

            // Generate a token for the channel
            $token = $channel->createToken($data['name']);
            $channel->update([
                'token_id' => $token->accessToken->id,
                'access_token' => $token->plainTextToken,
            ]);
        }
    }
}
