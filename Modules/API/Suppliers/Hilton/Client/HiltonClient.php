<?php

namespace Modules\API\Suppliers\Hilton\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HiltonClient
{
    private const BASE_URL = 'https://kapip-s.hilton.io/hospitality-partner/v2';

    private const TOKEN_URL = 'https://kapip-s.hilton.io/hospitality-partner/v2/realms/applications/token';

    private const DERBYSOFT_URL = 'https://htej.derbysoftsec.com/hse-json/api/dcshop/props';

    private Client $client;

    private ?string $accessToken;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10.0]);
        try {
            $this->authenticate();
        } catch (Exception $e) {
            Log::error('HiltonClient Initialization Failed: '.$e->getMessage());
            throw new Exception('Failed to initialize HiltonClient: '.$e->getMessage());
        }
    }

    private function authenticate(): void
    {
        $this->accessToken = Cache::get('hilton_access_token');
        if (! $this->accessToken) {
            try {
                $response = $this->client->post(self::TOKEN_URL, [
                    'form_params' => [
                        'client_id' => config('services.hilton.client_id', env('HILTON_CLIENT_ID')),
                        'client_secret' => config('services.hilton.client_secret', env('HILTON_CLIENT_SECRET')),
                        'grant_type' => 'client_credentials',
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $this->accessToken = $data['access_token'] ?? '';

                if ($this->accessToken) {
                    Cache::put('hilton_access_token', $this->accessToken, now()->addSeconds($data['expires_in'] - 60));
                } else {
                    throw new Exception('Hilton API did not return an access token');
                }
            } catch (GuzzleException|Exception $e) {
                Log::error('Hilton API Authentication Failed: '.$e->getMessage());
                throw new Exception('Failed to authenticate with Hilton API: '.$e->getMessage());
            }
        }
    }

    private function request(string $method, string $uri, array $options = [], string $baseUrl = self::BASE_URL): array
    {
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => 'Bearer '.$this->accessToken,
            'Content-Type' => 'application/json',
        ]);

        try {
            $response = $this->client->request($method, $baseUrl.$uri, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException|Exception $e) {
            Log::error('Hilton API Request Failed: '.$e->getMessage());
            if (str_contains($e->getMessage(), '401')) {
                Cache::forget('hilton_access_token');
                $this->authenticate();

                return $this->request($method, $uri, $options, $baseUrl);
            }

            return ['error' => $e->getMessage()];
        }
    }

    public function getTotalPropertiesCount(): int
    {
        $response = $this->request('GET', '/content/propsextracts?offset=0&limit=1');

        if (isset($response['error'])) {
            return 0;
        }

        // Get headers directly from the response
        $headers = $this->client->request('GET', self::BASE_URL.'/content/propsextracts?offset=0&limit=1')->getHeaders();

        if (isset($headers['Content-Range'][0])) {
            preg_match('/items \d+-\d+\/(\d+)/', $headers['Content-Range'][0], $matches);

            return isset($matches[1]) ? (int) $matches[1] : 0;
        }

        return 0;
    }

    public function getAllPropertyExtracts(int $limit = 50): array
    {
        $allProperties = [];
        $offset = 0;

        do {
            $properties = $this->getProperties($offset, $limit);

            if (isset($properties['error'])) {
                // Stop the loop if an error occurs
                break;
            }

            if (! empty($properties)) {
                $allProperties = array_merge($allProperties, $properties);
                $offset += $limit;
            }
        } while (count($properties) === $limit); // Continue while full batch is returned

        return $allProperties;
    }

    public function getProperties(int $offset = 0, int $limit = 50): array
    {
        return $this->request('GET', "/content/propsextracts?offset=$offset&limit=$limit");
    }

    public function getProperty(string $propCode): array
    {
        return $this->request('GET', "/content/propsextracts?propCode=$propCode");
    }

    public function multiPropertyShop(array $params): array
    {
        return $this->request('POST', '/dcshop/props', ['json' => $params]);
    }

    public function singlePropertyShop(string $propCode, array $params): array
    {
        return $this->request('POST', "/dcshop/props/$propCode", ['json' => $params]);
    }

    public function liveCheckRoom(string $propCode, array $params): array
    {
        return $this->request('POST', "/dcshop/props/$propCode", ['json' => $params]);
    }

    public function createReservation(array $reservationData): array
    {
        return $this->request('POST', '/dcres', ['json' => $reservationData]);
    }

    public function createMultiRoomReservation(array $reservationData): array
    {
        return $this->request('POST', '/dcres', ['json' => $reservationData]);
    }

    public function getReservation(string $confNumber, string $gnrNumber): array
    {
        return $this->request('GET', "/dcres/$confNumber/gnr/$gnrNumber");
    }

    public function getMultiRoomReservation(string $confNumber): array
    {
        return $this->request('GET', "/dcres/$confNumber");
    }

    public function modifyReservation(string $confNumber, string $gnrNumber, array $data): array
    {
        return $this->request('PUT', "/dcres/$confNumber/gnr/$gnrNumber", ['json' => $data]);
    }

    public function cancelReservation(string $confNumber, string $gnrNumber): array
    {
        return $this->request('POST', "/dcres/$confNumber/gnr/$gnrNumber/cancel", ['json' => []]);
    }

    public function derbysoftMultiPropertyShop(array $params): array
    {
        return $this->request('POST', '', ['json' => $params], self::DERBYSOFT_URL);
    }
}
