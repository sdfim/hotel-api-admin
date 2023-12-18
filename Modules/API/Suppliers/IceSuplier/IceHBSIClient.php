<?php

namespace Modules\API\Suppliers\IceSuplier;

use Illuminate\Support\Facades\Http;

class IceHBSIClient
{
    private string $clientId;

    private string $clientSecret;

    private string $baseUrl;

    private string $tokenUrl;

    private string $token;

    public function __construct()
    {
        $this->clientId = 'cassawave.api';
        $this->clientSecret = 'ydysxstyeztedxtrOiyqijOM';
        $this->baseUrl = 'https://api.iceportal.com';
        $this->tokenUrl = 'https://auth.iceportal.com/connect/token';
        $this->token = $this->getToken();
    }

    /**
     * @throws \Exception
     */
    private function getToken(): string
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new \Exception('Unable to retrieve token');
    }

    public function get(string $endpoint, array $query = [])
    {
        return Http::withToken($this->token)->get($this->baseUrl.$endpoint, $query);
    }

    public function post(string $endpoint, array $data = [])
    {
        return Http::withToken($this->token)->post($this->baseUrl.$endpoint, $data);
    }

    public function pool($callback)
    {
        return Http::pool($callback);
    }

    /**
     * @throws \Exception
     */
    public function fetchToken(): string
    {
        return $this->getToken();
    }

    /**
     * @param string $endpoint
     * @return string
     */
    public function url(string $endpoint): string
    {
        return $this->baseUrl.$endpoint;
    }

    // Add other methods (put, delete, etc.) as needed
}
