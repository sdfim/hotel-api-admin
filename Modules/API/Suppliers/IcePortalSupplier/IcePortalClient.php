<?php

namespace Modules\API\Suppliers\IcePortalSupplier;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IcePortalClient
{
    private string $clientId;

    private string $clientSecret;

    private string $baseUrl;

    private string $tokenUrl;

    private string $token;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $namespace = 'booking-suppliers.IcePortal.credentials';
        $this->clientId = config("$namespace.client_id");
        $this->clientSecret = config("$namespace.client_secret");
        $this->baseUrl = config("$namespace.base_url");
        $this->tokenUrl = config("$namespace.token_url");
        $this->token = $this->getToken();
    }

    /**
     * @throws Exception
     */
    private function getToken(): string
    {
        if (Cache::has('ice_portal_token')) {
            return Cache::get('ice_portal_token');
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            Cache::put('ice_portal_token', $response->json()['access_token'], 24 * 60 * 60);

            return $response->json()['access_token'];
        }

        throw new Exception('Unable to retrieve token');
    }

    public function get(string $endpoint, array $query = []): PromiseInterface|Response
    {
        return Http::withToken($this->token)->get($this->baseUrl.$endpoint, $query);
    }

    public function post(string $endpoint, array $data = []): PromiseInterface|Response
    {
        return Http::withToken($this->token)->post($this->baseUrl.$endpoint, $data);
    }

    public function pool($callback): array
    {
        return Http::pool($callback);
    }

    /**
     * @throws Exception
     */
    public function fetchToken(): string
    {
        return $this->getToken();
    }

    public function url(string $endpoint): string
    {
        return $this->baseUrl.$endpoint;
    }
}
