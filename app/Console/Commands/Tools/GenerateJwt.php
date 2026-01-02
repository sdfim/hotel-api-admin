<?php

namespace App\Console\Commands\Tools;

use Firebase\JWT\JWT;
use Illuminate\Console\Command;

class GenerateJwt extends Command
{
    protected $signature = 'jwt:generate {email} {sub} {first_name} {last_name}';

    protected $description = 'Generate a JWT token with the provided claims';

    public function handle()
    {
        $email = $this->argument('email');
        $sub = $this->argument('sub');
        $firstName = $this->argument('first_name');
        $lastName = $this->argument('last_name');

        $key = config('jwt.secret');
        $payload = [
            'email' => $email,
            'externalCustomerId' => $sub,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'exp' => time() + 3600, // Token expires in 1 hour
            'iat' => time(), // Token issued at
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');

        $this->info('Generated JWT: '.$jwt);
    }
}
