<?php

namespace Modules\API\Suppliers\Oracle\Client;

use App\Jobs\SaveSearchInspector;
use Exception;
use Fiber;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OracleClient
{
    private Credentials $credentials;

    protected string $token;

    protected array $headers;

    public function __construct(
        private readonly Client $client,
    ) {
        $this->credentials = CredentialsFactory::fromConfig();

        try {
            $this->token = $this->fetchToken();
        } catch (Exception $e) {
            Log::error('OracleClient: Failed to fetch authentication token: '.$e->getMessage());
            throw new Exception('OracleClient: Initialization failed due to authentication token error.');
        } catch (GuzzleException $e) {
            Log::error('OracleClient: Guzzle error while fetching authentication token: '.$e->getMessage());
            throw new Exception('OracleClient: Initialization failed due to Guzzle error during token fetching.');
        }

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->token,
            'x-app-key' => $this->credentials->appKey,
            // 'x-hotelid' будет устанавливаться динамически в методе getPropertiesByIds
        ];
    }

    /**
     * Fetches an OAuth token from the Oracle API and caches it until expiration.
     *
     * @return string|null The fetched token or null on failure.
     *
     * @throws GuzzleException
     */
    public function fetchToken(): ?string
    {
        $cacheKey = 'oracle_api_token';
        $cachedToken = cache($cacheKey);

        if ($cachedToken) {
            Log::info('OracleClient: Token found in cache.');

            return $cachedToken;
        }

        $credentials = CredentialsFactory::fromConfig();
        $basicAuthHeader = 'Basic '.base64_encode($credentials->basicUsername.':'.$credentials->basicPassword);

        try {
            $response = $this->client->request('POST', $credentials->baseUrl.'/oauth/v1/tokens', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'x-app-key' => $credentials->appKey,
                    'Authorization' => $basicAuthHeader,
                ],
                'form_params' => [
                    'username' => $credentials->username,
                    'password' => $credentials->password,
                    'grant_type' => 'password',
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            $response->getBody()->rewind();

            $data = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('OracleClient: Failed to decode JSON response.', [
                    'json_error' => json_last_error_msg(),
                    'response_body' => $responseBody,
                ]);

                return null;
            }

            if (isset($data['access_token'], $data['expires_in'])) {
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'];

                cache([$cacheKey => $token], now()->addSeconds($expiresIn));

                Log::info("OracleClient: Token successfully fetched and cached for $expiresIn seconds.");

                return $token;
            }

            Log::warning('OracleClient: Successful response (200 OK) but access_token or expires_in missing in response.', [
                'response_data' => $data,
            ]);

        } catch (GuzzleException $e) {
            $responseErrorBody = null;
            if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
                $responseErrorBody = $e->getResponse()->getBody()->getContents();
            }
            Log::error('OracleClient: Guzzle error during token fetching.', [
                'exception_message' => $e->getMessage(),
                'response_body' => $responseErrorBody,
            ]);
        } catch (Exception $e) {
            Log::error('OracleClient: Failed to fetch Oracle API token.', ['exception_message' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Searches for availability and prices by a list of property IDs using the Oracle REST API.
     * Uses Guzzle async and Fiber::suspend to wait for multiple requests.
     *
     * @param  array  $hotelIds  The property IDs (Hotels) to search.
     * @param  array  $filters  Search criteria (checkin, checkout, occupancy).
     * @param  array  $searchInspector  Inspector metadata.
     * @return array|null Search results or error information.
     */
    public function getPriceByPropertyIds(array $hotelIds, array $filters, array $searchInspector): ?array
    {
        $allResults = [];
        $errors = [];

        $checkin = Arr::get($filters, 'checkin');
        $checkout = Arr::get($filters, 'checkout');
        $occupancy = Arr::get($filters, 'occupancy');
        $ratePlanCode = Arr::get($filters, 'ratePlanCode');

        // Валидация
        if (empty($hotelIds) || ! $checkin || ! $checkout || empty($occupancy)) {
            Log::error('OracleClient: Missing required search parameters.');

            return ['error' => 'Missing required search parameters (hotelIds, checkin, checkout, occupancy).'];
        }

        /** @var PromiseInterface[] $promises */
        $promises = [];

        // Для логирования оригинальных запросов
        $originalRequests = [];

        /**
         * 1. Формирование асинхронных запросов
         * Один availability = одна комната
         */
        foreach ($hotelIds as $hotelId) {
            foreach ($occupancy as $roomIndex => $roomConfig) {

                $guestDetails = $this->mapSingleRoomToOracleQuery($roomConfig);

                $queryParams = [
                    'roomStayStartDate' => $checkin,
                    'roomStayEndDate' => $checkout,
                    'roomStayQuantity' => 1,
                    'adults' => $guestDetails['adults'],
                    'children' => $guestDetails['children'],
                    'ratePlanInfo' => 'true',
                    'resGuaranteeInfo' => 'true',
                    'roomTypeInfo' => 'true',
                    'currencyCode' => 'USD',
                    'roomType' => $guestDetails['roomType'],
                    'ratePlanCode' => $guestDetails['ratePlanCode'],
                ];

                if (! empty($ratePlanCode)) {
                    $queryParams['ratePlanCode'] = $ratePlanCode;
                }

                //                if (! empty($guestDetails['childAge'])) {
                //                    $queryParams['childAge'] = $guestDetails['childAge'];
                //                }

                $url = $this->credentials->baseUrl
                    ."/par/v1/hotels/{$hotelId}/availability?"
                    .http_build_query($queryParams);

                $headers = $this->headers;
                $headers['x-hotelid'] = $hotelId;

                $requestMeta = [
                    'hotelId' => $hotelId,
                    'roomIndex' => $roomIndex,
                    'url' => $url,
                    'headers' => $headers,
                    'method' => 'GET',
                ];

                $originalRequests[] = $requestMeta;

                $promises[] = $this->client->getAsync($url, [
                    'headers' => $headers,
                    'timeout' => config('services.oracle.timeout', 60),
                ])->then(
                    function ($response) use ($requestMeta) {
                        return [
                            'status' => 'fulfilled',
                            'meta' => $requestMeta,
                            'response' => $response,
                        ];
                    },
                    function ($reason) use ($requestMeta) {
                        return [
                            'status' => 'rejected',
                            'meta' => $requestMeta,
                            'error' => $reason,
                        ];
                    }
                );
            }
        }

        try {
            /**
             * 2. Ожидание всех запросов
             */
            $results = Fiber::suspend($promises);

            /**
             * 3. Обработка результатов
             */
            foreach ($results as $result) {
                $meta = $result['meta'];
                $hotelId = $meta['hotelId'];
                $roomIndex = $meta['roomIndex'];

                if ($result['status'] === 'rejected') {
                    $errors[] = [
                        'hotelId' => $hotelId,
                        'roomIndex' => $roomIndex,
                        'error' => (string) $result['error'],
                    ];

                    $this->dispatchSearchError(
                        $searchInspector,
                        'Oracle request failed (Room '.$roomIndex.')',
                        $hotelId,
                        ['request' => $meta, 'error' => (string) $result['error']]
                    );

                    continue;
                }

                /** @var ResponseInterface $response */
                $response = $result['response'];
                $response->getBody()->rewind();
                $body = $response->getBody()->getContents();

                $responseData = json_decode($body, true);

                if (isset($responseData['errors'])) {
                    $errors[] = [
                        'hotelId' => $hotelId,
                        'roomIndex' => $roomIndex,
                        'error' => $responseData['errors'],
                    ];

                    $this->dispatchSearchError(
                        $searchInspector,
                        'Oracle API error (Room '.$roomIndex.')',
                        $hotelId,
                        ['request' => $meta, 'response' => $responseData]
                    );

                    continue;
                }

                if (! isset($allResults[$hotelId])) {
                    $allResults[$hotelId] = [];
                }

                $allResults[$hotelId]['room_'.$roomIndex] = $responseData;
            }

        } catch (ConnectException $e) {
            Log::error('Oracle Connection timeout during batch search: '.$e->getMessage());

            return ['error' => 'Connection timeout'];
        } catch (ServerException $e) {
            Log::error('Oracle Server error during batch search: '.$e->getMessage());

            return ['error' => 'Server error'];
        } catch (Throwable $e) {
            Log::error('Oracle Unexpected error during batch search: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }

        return [
            'request' => [
                'hotelIds' => $hotelIds,
                'filters' => $filters,
                'original_requests' => $originalRequests,
            ],
            'response' => $allResults,
            'errors' => $errors,
        ];
    }

    /**
     * Вспомогательный метод для маппинга фильтров Occupancy ОДНОЙ комнаты в параметры запроса Oracle.
     * Заменяет mapOccupancyToOracleQuery.
     */
    protected function mapSingleRoomToOracleQuery(array $roomConfig): array
    {
        $totalAdults = Arr::get($roomConfig, 'adults', 0);
        $childrenAges = Arr::get($roomConfig, 'children_ages', []);
        $totalChildren = count($childrenAges);

        $roomType = Arr::get($roomConfig, 'room_type', null);
        $ratePlanCode = Arr::get($roomConfig, 'rate_plan_code', null);

        return [
            'adults' => $totalAdults,
            'children' => $totalChildren,
            'childAge' => implode(',', $childrenAges),
            'roomType' => $roomType,
            'ratePlanCode' => $ratePlanCode,
        ];
    }

    /**
     * Вспомогательный метод для маппинга фильтров Occupancy в параметры запроса Oracle.
     * Oracle API в примере использует общие 'adults', 'children', 'roomStayQuantity'.
     */
    protected function mapOccupancyToOracleQuery(array $occupancy): array
    {
        $totalRooms = count($occupancy);
        $totalAdults = 0;
        $totalChildren = 0;
        $allChildAges = [];

        foreach ($occupancy as $room) {
            $totalAdults += Arr::get($room, 'adults', 0);
            $childrenAges = Arr::get($room, 'children_ages', []);
            $totalChildren += count($childrenAges);
            $allChildAges = array_merge($allChildAges, $childrenAges);
        }

        return [
            'roomStayQuantity' => $totalRooms,
            'adults' => $totalAdults,
            'children' => $totalChildren,
            'childAge' => implode(',', $allChildAges),
        ];
    }

    /**
     * Вспомогательный метод для отправки SaveSearchInspector при ошибке.
     */
    protected function dispatchSearchError(array $searchInspector, string $message, string $hotelId, array $original = []): void
    {
        $parent_search_id = $searchInspector['search_id'];
        $searchInspector['search_id'] = Str::uuid();
        $original = array_merge($original, ['hotelId' => $hotelId]);

        SaveSearchInspector::dispatch($searchInspector, $original, [], [], 'error',
            ['side' => 'supplier', 'message' => $message, 'parent_search_id' => $parent_search_id]);
    }
}
