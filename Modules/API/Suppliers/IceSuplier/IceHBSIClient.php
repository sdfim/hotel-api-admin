<?php

namespace Modules\API\Suppliers\IceSuplier;

use App\Jobs\SendEmailCredentialsAlert;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IceHBSIClient
{
    /**
     * Number of seconds in one day.
     */
    private const ONE_DAY_IN_SECONDS = 86400; // 24 * 60 * 60

    private string $clientId;

    private string $clientSecret;

    private string $baseUrl;

    private string $tokenUrl;

    /**
     * @var string|null The access token.
     */
    private ?string $token = null;

    /**
     * Constructor.
     *
     * Initializes the client with configuration values.
     */
    public function __construct()
    {
        $namespace = 'booking-suppliers.IcePortal.credentials';
        $this->clientId = config("$namespace.client_id");
        $this->clientSecret = config("$namespace.client_secret");
        $this->baseUrl = config("$namespace.base_url");
        $this->tokenUrl = config("$namespace.token_url");
    }

    /**
     * Retrieves the access token.
     *
     * @return string The access token.
     *
     * @throws Exception If unable to retrieve the token.
     */
    private function getToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        if (Cache::has('ice_portal_token')) {
            $this->token = Cache::get('ice_portal_token');
            return $this->token;
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);
    
        if ($response->successful()) {
            $this->token = $response->json()['access_token'];
            Cache::put('ice_portal_token', $this->token, self::ONE_DAY_IN_SECONDS);
            return $this->token;
        }

        $errorData = $response->json();
        $errorString = json_encode($errorData) ?: 'Unable to retrieve token';

        SendEmailCredentialsAlert::dispatch('iceportal', $errorString);

        throw new Exception($errorString);
    }

    public function get(string $endpoint, array $query = []): PromiseInterface|Response
    {
        return Http::withToken($this->getToken())->get($this->baseUrl.$endpoint, $query);
    }

    public function post(string $endpoint, array $data = []): PromiseInterface|Response
    {
        return Http::withToken($this->getToken())->post($this->baseUrl.$endpoint, $data);
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
