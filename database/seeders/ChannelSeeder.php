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
        if (!Channel::first()) {
            if (Auth::attempt(['email' => 'admin@ujv.com', 'password' => 'C5EV0gEU9OnlS5r'])) {
                $user = Auth::user();
                $data = [];
                $data['name'] = 'Test Channel';
                $data['description'] = 'Test Channel Description';
                $token = $user->createToken($data['name']);
                $data['token_id'] = $token->accessToken->id;
                $data['access_token'] = $token->plainTextToken;
                Channel::create($data);
            }
        }
    }
}
