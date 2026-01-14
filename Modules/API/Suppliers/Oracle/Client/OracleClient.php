<?php

namespace Modules\API\Suppliers\Oracle\Client;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveSearchInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingsMetadata;
use App\Repositories\ApiBookingInspectorRepository;
use Exception;
use Fiber;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;
use Throwable;

class OracleClient
{
    private Credentials $credentials;

    protected ?string $token = null;

    protected ?array $lastTokenError = null;

    protected array $headers = [];

    public function __construct(
        private readonly Client $client,
    ) {
        $this->credentials = CredentialsFactory::fromConfig();
    }

    private function ensureToken(): void
    {
        if ($this->token !== null) {
            return;
        }

        $this->token = $this->fetchToken();

        if ($this->token === null) {
            throw new Exception('OracleClient: Initialization failed due to authentication token error.');
        }

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->token,
            'x-app-key' => $this->credentials->appKey,
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

        $requestMeta = [
            'url' => $credentials->baseUrl.'/oauth/v1/tokens',
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
        ];

        try {
            $response = $this->client->request('POST', $requestMeta['url'], [
                'headers' => $requestMeta['headers'],
                'form_params' => $requestMeta['form_params'],
            ]);

            $responseBody = $response->getBody()->getContents();
            $response->getBody()->rewind();

            $data = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->lastTokenError = [
                    'request' => $requestMeta,
                    'response' => $responseBody,
                    'error' => 'Failed to decode JSON response: '.json_last_error_msg(),
                ];
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

            $this->lastTokenError = [
                'request' => $requestMeta,
                'response' => $data,
                'error' => 'access_token or expires_in missing in response',
            ];
            Log::warning('OracleClient: Successful response (200 OK) but access_token or expires_in missing in response.', [
                'response_data' => $data,
            ]);

        } catch (GuzzleException $e) {
            $responseErrorBody = null;
            if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
                $responseErrorBody = $e->getResponse()->getBody()->getContents();
            }

            $this->lastTokenError = [
                'request' => $requestMeta,
                'response' => json_decode($responseErrorBody, true) ?: $responseErrorBody,
                'error' => $e->getMessage(),
            ];

            Log::error('OracleClient: Guzzle error during token fetching.', [
                'exception_message' => $e->getMessage(),
                'response_body' => $responseErrorBody,
            ]);
        } catch (Exception $e) {
            $this->lastTokenError = [
                'request' => $requestMeta,
                'error' => $e->getMessage(),
            ];
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
        try {
            $this->ensureToken();
        } catch (Exception $e) {
            $this->dispatchSearchError(
                $searchInspector,
                $e->getMessage(),
                'N/A',
                $this->lastTokenError ?: ['error' => $e->getMessage()]
            );

            return ['error' => $e->getMessage(), 'token_error' => $this->lastTokenError];
        }

        $allResults = [];
        $errors = [];

        $checkin = Arr::get($filters, 'checkin');
        $checkout = Arr::get($filters, 'checkout');
        $occupancy = Arr::get($filters, 'occupancy');

        // Validation
        if (empty($hotelIds) || ! $checkin || ! $checkout || empty($occupancy)) {
            Log::error('OracleClient: Missing required search parameters.');

            return ['error' => 'Missing required search parameters (hotelIds, checkin, checkout, occupancy).'];
        }

        /** @var PromiseInterface[] $promises */
        $promises = [];

        // For logging original requests
        $originalRequests = [];

        /**
         * 1. Formation of asynchronous requests
         * One availability = one room
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
             * 2. Waiting for all requests
             */
            $results = Fiber::suspend($promises);

            /**
             * 3. Processing the results
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
     * Searches for availability and prices by a list of property IDs synchronously (without Fiber).
     * Executes a series of synchronous requests, one for each room/occupancy configuration.
     *
     * @param  array  $hotelIds  The property IDs (Hotels) to search.
     * @param  array  $filters  Search criteria (checkin, checkout, occupancy).
     * @param  array  $searchInspector  Inspector metadata.
     * @return array|null Search results or error information.
     */
    public function getSyncPriceByPropertyIds(array $hotelIds, array $filters, array $searchInspector): ?array
    {
        try {
            $this->ensureToken();
        } catch (Exception $e) {
            $this->dispatchSearchError(
                $searchInspector,
                $e->getMessage(),
                'N/A',
                $this->lastTokenError ?: ['error' => $e->getMessage()]
            );

            return ['error' => $e->getMessage(), 'token_error' => $this->lastTokenError];
        }

        $allResults = [];
        $errors = [];
        $originalRequests = [];

        $checkin = Arr::get($filters, 'checkin');
        $checkout = Arr::get($filters, 'checkout');
        $occupancy = Arr::get($filters, 'occupancy');

        // Validation
        if (empty($hotelIds) || ! $checkin || ! $checkout || empty($occupancy)) {
            Log::error('OracleClient: Missing required search parameters.');

            return ['error' => 'Missing required search parameters (hotelIds, checkin, checkout, occupancy).'];
        }

        /**
         * 1. Formation and execution of synchronous requests
         * One availability = one room
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
                    // Important: for GET requests there is no body, so body: null
                    'body' => null,
                ];

                $originalRequests[] = $requestMeta;
                $requestBodyForInspector = null; // Для GET запроса

                // 2. Determining the API callback for the request (GET)
                $apiCall = function () use ($url, $headers) {
                    return $this->client->get($url, [
                        'headers' => $headers,
                        'timeout' => config('services.oracle.timeout', 60),
                    ]);
                };

                // 3. Executing a synchronous request through a common wrapper
                $result = $this->executeSyncRequest(
                    $apiCall,
                    $searchInspector,
                    $requestMeta,
                    $requestBodyForInspector
                );

                if (isset($result['error'])) {
                    $errors[] = [
                        'hotelId' => $hotelId,
                        'roomIndex' => $roomIndex,
                        'error' => $result['error'],
                    ];

                    continue;
                }

                // Успешный результат
                $responseData = $result['response'];
                if (! isset($allResults[$hotelId])) {
                    $allResults[$hotelId] = [];
                }

                $allResults[$hotelId]['room_'.$roomIndex] = $responseData;
            }
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

    public function book($filters, $inspectorBook): array
    {
        $passengersData = ApiBookingInspectorRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        if (! $passengersData) {
            return [
                'error' => 'Passengers not found.',
                'request' => [],
                'response' => [],
                'main_guest' => [],
            ];
        }

        $guests = json_decode($passengersData->request, true)['rooms'] ?? [];
        $commentsByFilter = Arr::get($filters, 'comments');
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();

        if (! $bookingItem || ! $bookingItem->search) {
            return [
                'error' => 'Booking item or search record not found.',
                'request' => [],
                'response' => [],
                'main_guest' => [],
            ];
        }

        $bookingItemData = json_decode($bookingItem->booking_item_data, true);
        $supplierHotelId = Arr::get($bookingItemData, 'hotel_supplier_id');

        $request = json_decode($bookingItem->search->request, true);

        // Extracting required dates
        $startDate = Arr::get($request, 'checkin');
        $endDate = Arr::get($request, 'checkout');

        $mappedGuests = [];
        $bodyQuery = [];
        $roomIndex = -1;

        // make request reservations for each room
        foreach ($guests as $roomIndex => $roomGuests) {
            if ($roomIndex === 0) {
                $mappedGuests = $roomGuests;
            }

            $reservationGuests = $this->mapGuests($roomGuests);

            $roomStay = $this->getRoomStay($supplierHotelId, $bookingItem, $roomIndex, $roomGuests);

            if (empty($roomStay) || ! isset($roomStay['guarantee'])) {
                Log::error("OracleClient: Failed to retrieve room stay data or guarantee info for booking item {$filters['booking_item']}");

                return ['error' => 'Failed to prepare reservation data.'];
            }

            $guaranteeInfo = $roomStay['guarantee'];
            unset($roomStay['guarantee']);

            $comments = $commentsByFilter
                ? $this->mapComments($commentsByFilter, $bookingItem->booking_item, $roomIndex + 1)
                : [];

            $bodyQuery['reservations']['reservation'][] = $this->makeBookRequestBody(
                $supplierHotelId,
                $roomStay,
                $reservationGuests,
                $comments,
                $startDate,
                $endDate,
                $guaranteeInfo
            );
        }

        // Checking that at least one room is processed
        if (empty($bodyQuery)) {
            return [
                'error' => 'No rooms processed for booking request.',
                'request' => [],
                'response' => [],
                'main_guest' => [],
            ];
        }

        $url = $this->credentials->baseUrl.'/rsv/v1/hotels/'.$supplierHotelId.'/reservations';

        try {
            $this->ensureToken();
        } catch (Exception $e) {
            $this->dispatchBookingError(
                $inspectorBook,
                $e->getMessage(),
                ['original' => ['request' => $bodyQuery, 'response' => $this->lastTokenError ?: $e->getMessage()]]
            );

            return [
                'error' => $e->getMessage(),
                'token_error' => $this->lastTokenError,
                'request' => [],
                'response' => [],
                'main_guest' => $mappedGuests,
                'errors' => $this->lastTokenError ?: [$e->getMessage()],
            ];
        }
        $headers = $this->headers;
        $headers['x-hotelid'] = $supplierHotelId;

        $apiCall = function () use ($url, $headers, $bodyQuery) {
            return $this->client->post($url, [
                'headers' => $headers,
                'timeout' => config('services.oracle.timeout', 60),
                'json' => $bodyQuery,
            ]);
        };

        $requestMeta = [
            'hotelId' => $supplierHotelId,
            'roomIndex' => $roomIndex,
            'url' => $url,
            'headers' => $headers,
            'method' => 'POST',
            'body' => $bodyQuery,
        ];

        $response = $this->executeBookingSyncRequest(
            $apiCall,
            $inspectorBook,
            $requestMeta,
            $bodyQuery
        );

        return [
            'request' => $requestMeta,
            'response' => Arr::get($response, 'response.links', []),
            'main_guest' => $mappedGuests,
            'errors' => Arr::get($response, 'response.errors', []) ?: Arr::get($response, 'error', []),
        ];
    }

    /**
     * Retrieves a single reservation by its ID (operationId: getReservation).
     *
     * Corresponds to the request:
     * href: "https://.../rsv/v1/hotels/VIRM/reservations/22780628"
     * method: "GET"
     *
     * @param  ApiBookingsMetadata  $apiBookingsMetadata  Booking metadata for extracting the ID.
     * @param  array  $inspector  Metadata for logging (Inspector).
     * @return array|null API response as an array ('response' section) or null on error.
     *
     * @throws Exception
     */
    public function retrieve(ApiBookingsMetadata $apiBookingsMetadata, array $inspector): ?array
    {
        $booking_id = $apiBookingsMetadata->booking_id;
        $booking_item = $apiBookingsMetadata->booking_item;
        $hotelId = $apiBookingsMetadata->hotel_supplier_id;
        $path = ApiBookingInspectorRepository::bookedItem($booking_id, $booking_item)->client_response_path;
        $reservationId = json_decode(Storage::get($path), true)['confirmation_numbers_list']['reservationNumber'] ?? '';

        $url = $this->credentials->baseUrl."/rsv/v1/hotels/{$hotelId}/reservations/{$reservationId}";

        try {
            $this->ensureToken();
        } catch (Exception $e) {
            $this->dispatchBookingError(
                $inspector,
                $e->getMessage(),
                ['original' => ['request' => ['hotelId' => $hotelId, 'reservationId' => $reservationId], 'response' => $this->lastTokenError ?: $e->getMessage()]]
            );

            return [
                'error' => $e->getMessage(),
                'token_error' => $this->lastTokenError,
                'request' => ['hotelId' => $hotelId, 'reservationId' => $reservationId],
                'response' => null,
            ];
        }
        $headers = $this->headers;
        $headers['x-hotelid'] = $hotelId;

        $requestMeta = [
            'hotelId' => $hotelId,
            'url' => $url,
            'headers' => $headers,
            'method' => 'GET',
            'body' => null,
        ];

        $apiCall = function () use ($url, $headers) {
            return $this->client->get($url, [
                'headers' => $headers,
                'timeout' => config('services.oracle.timeout', 60),
            ]);
        };

        $result = $this->executeBookingSyncRequest(
            $apiCall,
            $inspector,
            $requestMeta,
            null // No body for GET request
        );

        return [
            'request' => $requestMeta,
            'response' => Arr::get($result, 'response'),
        ];
    }

    /**
     * Cancels a reservation by its ID (operationId: cancelReservation).
     *
     * Corresponds to the request:
     * href: "https://.../rsv/v1/hotels/VIRM/reservations/22780628/cancellations"
     * method: "POST"
     *
     * @param  ApiBookingsMetadata  $apiBookingsMetadata  Booking metadata for extracting the ID.
     * @param  array  $inspectorCancel  Metadata for logging (Booking Inspector).
     * @return array|null API response as an array ('response' section) or null on error.
     *
     * @throws Exception If ReservationId or token cannot be found.
     */
    public function cancel(ApiBookingsMetadata $apiBookingsMetadata, array $inspectorCancel): ?array
    {
        $booking_id = $apiBookingsMetadata->booking_id;
        $booking_item = $apiBookingsMetadata->booking_item;
        $hotelId = $apiBookingsMetadata->hotel_supplier_id;

        $path = ApiBookingInspectorRepository::bookedItem($booking_id, $booking_item)->client_response_path;
        $reservationId = json_decode(Storage::get($path), true)['confirmation_numbers_list']['reservationNumber'] ?? '';

        if (empty($reservationId)) {
            Log::error("OracleClient: Cannot cancel, ReservationId not found for booking item {$booking_item}");
            $this->dispatchBookingError(
                $inspectorCancel,
                'Reservation ID not found in stored response.',
                ['original' => ['request' => ['booking_id' => $booking_id, 'booking_item' => $booking_item, 'hotelId' => $hotelId], 'response' => 'Reservation ID missing.']]
            );

            return null;
        }

        try {
            $this->ensureToken();
        } catch (Exception $e) {
            $this->dispatchBookingError(
                $inspectorCancel,
                $e->getMessage(),
                ['original' => ['request' => ['hotelId' => $hotelId, 'reservationId' => $reservationId], 'response' => $this->lastTokenError ?: $e->getMessage()]]
            );

            return [
                'error' => $e->getMessage(),
                'token_error' => $this->lastTokenError,
            ];
        }

        $bodyQuery = [
            'reason' => [
                'code' => 'DUP',
                'description' => 'Trip Cancelled',
            ],
            'reservations' => [
                'reservationIdList' => [
                    'id' => $reservationId,
                    'type' => 'Reservation',
                ],
                'hotelId' => $hotelId,
            ],
        ];

        $endpoint = "/rsv/v1/hotels/{$hotelId}/reservations/{$reservationId}/cancellations";
        $url = $this->credentials->baseUrl.$endpoint;
        $headers = $this->headers;
        $headers['x-hotelid'] = $hotelId;

        $requestMeta = [
            'hotelId' => $hotelId,
            'url' => $url,
            'headers' => $headers,
            'method' => 'POST',
            'body' => $bodyQuery,
        ];

        $apiCall = function () use ($url, $headers, $bodyQuery) {
            return $this->client->post($url, [
                'headers' => $headers,
                'timeout' => config('services.oracle.timeout', 60),
                'json' => $bodyQuery,
            ]);
        };

        $result = $this->executeBookingSyncRequest(
            $apiCall,
            $inspectorCancel,
            $requestMeta,
            $bodyQuery
        );

        if (isset($result['error'])) {
            return ['error' => $result['error']];
        }

        return [
            'request' => $requestMeta,
            'response' => Arr::get($result, 'response'),
        ];
    }

    /**
     * Retrieves a list of reservations for a hotel by a list of confirmation numbers (operationId: getHotelReservations).
     *
     * Corresponds to the request:
     * href: "https://.../rsv/v1/hotels/VIRM/reservations?confirmationNumberList=588936260"
     * method: "GET"
     *
     * @param  string  $hotelId  Hotel code (e.g., 'VIRM').
     * @param  array  $confirmationNumbers  List of confirmation numbers (e.g., ['588936260', '...']).
     * @param  array  $inspector  Metadata for logging (Inspector).
     * @return array|null API response as an array ('response' section) or null on error.
     */
    public function getReservationsByConfirmationNumbers(string $hotelId, array $confirmationNumbers, array $inspector): ?array
    {
        try {
            $this->ensureToken();
        } catch (Exception $e) {
            if (! empty($inspector)) {
                $this->dispatchSearchError(
                    $inspector,
                    $e->getMessage(),
                    $hotelId,
                    $this->lastTokenError ?: ['error' => $e->getMessage()]
                );
            }

            return ['error' => $e->getMessage(), 'token_error' => $this->lastTokenError];
        }

        // Converting an array of confirmation numbers to a comma-separated string for the query parameter
        $confirmationNumberList = implode(',', $confirmationNumbers);

        // URL Parameters
        $queryParams = http_build_query([
            'confirmationNumberList' => $confirmationNumberList,
        ]);

        $endpoint = "/rsv/v1/hotels/{$hotelId}/reservations?".$queryParams;

        $url = $this->credentials->baseUrl.$endpoint;
        $headers = $this->headers;
        $headers['x-hotelid'] = $hotelId; // Adding the x-hotelid header

        $requestMeta = [
            'hotelId' => $hotelId,
            'url' => $url,
            'headers' => $headers,
            'method' => 'GET',
            'body' => null,
            'query_params' => $queryParams,
        ];

        // 2. Determining the API callback for the request (GET)
        $apiCall = function () use ($url, $headers) {
            return $this->client->get($url, [
                'headers' => $headers,
                'timeout' => config('services.oracle.timeout', 60),
            ]);
        };

        // 3. Executing a synchronous request
        $result = $this->executeSyncRequest(
            $apiCall,
            $inspector,
            $requestMeta,
            null // No body for GET request
        );

        return Arr::get($result, 'response');
    }

    public function countAdultsAndChildren(array $peopleArray, int $adultAgeThreshold = 18): array
    {
        $peopleCollection = collect($peopleArray);

        $adultsCount = $peopleCollection->filter(function ($person) use ($adultAgeThreshold) {
            $age = (int) ($person['age'] ?? 0);

            return $age >= $adultAgeThreshold;
        })->count();

        $totalPeople = $peopleCollection->count();
        $childrenCount = $totalPeople - $adultsCount;

        return [
            'adults' => (string) $adultsCount,
            'children' => (string) $childrenCount,
        ];
    }

    protected function getRoomStay(string $supplierHotelId, ApiBookingItem $bookingItem, int $roomIndex, array $roomGuests): array
    {
        $bookingItemData = json_decode($bookingItem->booking_item_data, true);
        $rateCodes = explode(';', Arr::get($bookingItemData, 'rate_code')) ?? [];
        $roomCodes = explode(';', Arr::get($bookingItemData, 'room_code')) ?? [];
        $roomKey = 'room_'.$roomIndex;
        $targetRatePlanCode = $rateCodes[$roomIndex] ?? '';
        $targetRoomType = $roomCodes[$roomIndex] ?? '';
        $pathRS = $bookingItem->search->original_path;
        $responseData = json_decode(Storage::get($pathRS), true);
        $supplierName = SupplierNameEnum::ORACLE->value;

        $roomData = data_get($responseData, "{$supplierName}.response.{$supplierHotelId}.{$roomKey}");

        if (empty($roomData) || ! isset($roomData['hotelAvailability'])) {
            Log::warning("OracleClient: Room data or hotelAvailability not found for booking item {$bookingItem->booking_item}");

            return [];
        }

        $roomRatesAdditionalFields = [
            'marketCode' => 'LEISURE',
            'marketCodeDescription' => 'Leisure',
            'sourceCode' => 'PHONE',
            'sourceCodeDescription' => 'Phone',
            'pseudoRoom' => false,
            'roomTypeCharged' => 'SUP',
            'houseUseOnly' => false,
            'complimentary' => false,
            'fixedRate' => true,
            'discountAllowed' => false,
            'bogoDiscount' => false,
        ];

        $guaranteeInfo = null;

        foreach ($roomData['hotelAvailability'] as $availability) {
            if (! isset($availability['roomStays'][0]['roomRates'])) {
                continue;
            }

            // 1. Finding a single matching rate and ignoring null elements
            $matchedRate = collect(Arr::get($availability['roomStays'][0], 'roomRates', []))
                ->filter() // Ignoring null elements, as in your example
                ->first(function ($rate) use ($targetRoomType, $targetRatePlanCode) {
                    // Searching for a rate matching roomType and ratePlanCode
                    return ($rate['roomType'] ?? null) === $targetRoomType &&
                        ($rate['ratePlanCode'] ?? null) === $targetRatePlanCode;
                });

            if ($matchedRate) {
                // Rate found. Updating it with additional fields.
                $updatedRate = array_merge($matchedRate, $roomRatesAdditionalFields);

                // Preparing the roomStay structure
                $foundRoomStay = $availability['roomStays'][0];

                // CRITICAL CHANGE: roomRates now contains ONLY the found rate
                $foundRoomStay['roomRates'] = [$updatedRate];

                // 2. Finding guarantee info (remains unchanged)
                $ratePlans = Arr::get($availability, 'masterInfo.ratePlans.ratePlan', []);
                $ratePlanInfo = collect($ratePlans)->firstWhere('ratePlanCode', $targetRatePlanCode);

                if ($ratePlanInfo) {
                    $defaultGuarantee = collect(Arr::get($ratePlanInfo, 'resGuarantees', []))
                        ->firstWhere('defaultGuarantee', true);

                    if ($defaultGuarantee) {
                        $guaranteeInfo = [
                            'guaranteeCode' => $defaultGuarantee['guaranteeCode'],
                            'shortDescription' => Arr::get($defaultGuarantee, 'shortDescription.defaultText', $defaultGuarantee['guaranteeCode']),
                        ];
                    }
                }

                $roomStay = $foundRoomStay;
                $roomStay['guestCounts'] = $this->countAdultsAndChildren($roomGuests);
                $roomStay['guarantee'] = $guaranteeInfo;

                return $roomStay;
            }
        }

        Log::warning("OracleClient: Matching roomStay (RoomType: {$targetRoomType}, RateCode: {$targetRatePlanCode}) not found.");

        return [];
    }

    protected function makeBookRequestBody(
        string $supplierHotelId,
        array $roomStay,
        array $reservationGuests,
        array $comments,
        string $arrivalDate,
        string $departureDate,
        array $guaranteeInfo
    ): array {
        $roomStay['arrivalDate'] = $arrivalDate;
        $roomStay['departureDate'] = $departureDate;
        $roomStay['guarantee'] = $guaranteeInfo;
        $roomStay['roomNumberLocked'] = false;
        $roomStay['printRate'] = false;

        return [
            'sourceOfSale' => [
                'sourceType' => 'PMS',
                'sourceCode' => $supplierHotelId,
            ],
            'roomStay' => $roomStay,
            'reservationGuests' => $reservationGuests,
            'reservationPaymentMethods' => [
                'paymentMethod' => 'EFCMXN',
                'folioView' => '1',
            ],
            'comments' => $comments,
            'hotelId' => $supplierHotelId,
            'roomStayReservation' => true,
            'reservationStatus' => 'Reserved',
            'computedReservationStatus' => 'DueIn',
            'walkIn' => false,
            'printRate' => false,
            'preRegistered' => false,
            'upgradeEligible' => false,
            'allowAutoCheckin' => false,
            'hasOpenFolio' => false,
            'allowMobileCheckout' => false,
            'allowMobileViewFolio' => false,
            'allowPreRegistration' => false,
            'optedForCommunication' => false,
        ];
    }

    public function mapComments($commentsArray, string $bookingItemUuid, int $roomNumber): array
    {
        $commentsCollection = collect($commentsArray);

        $relevantComments = $commentsCollection->filter(function ($comment) use ($bookingItemUuid, $roomNumber) {
            return
                ($comment['booking_item'] ?? null) === $bookingItemUuid &&
                ($comment['room'] ?? null) === $roomNumber;
        });

        $formattedComments = $relevantComments->map(function ($comment) {
            return [
                'comment' => [
                    'text' => [
                        'value' => $comment['comment'] ?? 'No comment text provided',
                    ],
                    // These fields do not change and are taken from your example of the required format
                    'commentTitle' => 'General Notes',
                    'notificationLocation' => 'RESERVATION',
                    'type' => 'GEN',
                    'internal' => false,
                ],
            ];
        })->values()->toArray();

        return $formattedComments;
    }

    protected function mapGuests(array $inputArray): array
    {
        $profilesCollection = collect($inputArray);
        $formattedProfiles = $profilesCollection->map(function ($profileData) {
            $profileType = 'Guest';
            $personName = [
                'givenName' => $profileData['given_name'] ?? '',
                // middleName field not provided, leaving empty
                'middleName' => '',
                'surname' => $profileData['family_name'] ?? '',
                'nameType' => 'Primary',
            ];

            return [
                'profileInfo' => [
                    'profile' => [
                        'customer' => [
                            'personName' => [$personName],
                            'language' => 'E',
                        ],
                        'profileType' => $profileType,
                    ],
                ],
            ];
        })->toArray();

        return $formattedProfiles;
    }

    /**
     * Executes a single synchronous API request using a callable and handles common Guzzle exceptions.
     * This method serves as the common error handling wrapper for all synchronous requests (GET, POST, etc.).
     *
     * @param  callable  $apiCall  A function that executes the Guzzle request and returns a Response object.
     * @param  array  $inspector  Inspector metadata (either searchInspector or bookingInspector).
     * @param  array  $requestMeta  Metadata about the request (e.g., hotelId, roomIndex, url, headers, method).
     * @param  string|array|null  $bodyQuery  The request body/payload, used for logging in inspector.
     * @return array|null Returns ['response' => data] on success, or ['error' => message] on failure.
     */
    protected function executeSyncRequest(callable $apiCall, array $inspector, array $requestMeta, string|array|null $bodyQuery): ?array
    {
        // 1. Preparing data for Inspector
        $hotelId = Arr::get($requestMeta, 'hotelId');
        $roomIndex = Arr::get($requestMeta, 'roomIndex');

        $content['original']['request'] = $bodyQuery ?: $requestMeta; // Using the body for POST, or metadata for GET
        $content['original']['response'] = '';

        try {
            /** @var GuzzleResponse $response */
            $response = $apiCall();
            $body = $response->getBody()->getContents();
            $content['original']['response'] = $body;

            $responseData = json_decode($body, true);

            // 2. Checking HTTP status (although Guzzle throws ServerException for 5xx, it's better to check)
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
                // If Guzzle didn't throw an exception (e.g., on 4xx), handle it as an API error
                Log::error('Oracle API HTTP Error: '.$response->getStatusCode().' for '.$requestMeta['url']);

                $message = 'Oracle API HTTP Error: '.$response->getStatusCode().($responseData['errors'] ?? '');

                $this->dispatchSearchError(
                    $inspector,
                    $message,
                    $hotelId,
                    ['request' => $requestMeta, 'response' => $responseData]
                );

                return ['error' => $message];
            }

            // 3. API Error check (business logic error returned in 200 response body)
            if (isset($responseData['errors'])) {
                $errorDetails = json_encode($responseData['errors']);
                Log::error('Oracle API error (Sync): '.$errorDetails);

                $this->dispatchSearchError(
                    $inspector,
                    'Oracle API error (Room '.$roomIndex.'): '.$errorDetails,
                    $hotelId,
                    ['request' => $requestMeta, 'response' => $responseData]
                );

                return ['error' => $responseData['errors']];
            }

            // Success
            return ['response' => $responseData];

        } catch (ConnectException $e) {
            // 4. Timeout or connection error
            Log::error('Oracle Connection timeout (Sync): '.$e->getMessage());

            $this->dispatchSearchError(
                $inspector,
                'Connection timeout',
                $hotelId,
                $content['original']
            );

            return ['error' => 'Connection timeout'];
        } catch (ServerException $e) {
            // 5. Server error (5xx HTTP status code)
            Log::error('Oracle Server error (Sync): '.$e->getMessage());

            $this->dispatchSearchError(
                $inspector,
                'Oracle Server error',
                $hotelId,
                $content['original']
            );

            return ['error' => 'Server error'];
        } catch (Throwable $e) {
            // 6. Unexpected error (other exceptions)
            Log::error('Oracle Unexpected error (Sync): '.$e->getMessage());

            $this->dispatchSearchError(
                $inspector,
                'Unexpected error: '.$e->getMessage(),
                $hotelId,
                $content['original']
            );

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Executes a synchronous API request specialized for booking operations.
     * Handles errors and dispatches them via Booking Inspector.
     *
     * @param  callable  $apiCall  Function executing the request (e.g., $client->request(...)).
     * @param  array  $inspector  Booking Inspector object.
     * @param  array  $requestMeta  Request metadata.
     * @param  string|array|null  $bodyQuery  Request body.
     */
    protected function executeBookingSyncRequest(callable $apiCall, array $inspector, array $requestMeta, string|array|null $bodyQuery): ?array
    {
        // 1. Preparing data
        $hotelId = Arr::get($requestMeta, 'hotelId');
        $roomIndex = Arr::get($requestMeta, 'roomIndex');

        $content['original']['request'] = $bodyQuery ?: $requestMeta;
        $content['original']['response'] = '';

        try {
            /** @var GuzzleResponse $response */
            $response = $apiCall();
            $body = $response->getBody()->getContents();
            $content['original']['response'] = $body;
            $responseData = json_decode($body, true);

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
                $statusCode = $response->getStatusCode();
                Log::error('Oracle API HTTP Error (Booking): '.$statusCode.' for '.$requestMeta['url']);

                $message = 'Oracle API HTTP Error: '.$statusCode.' '.Arr::get($responseData, 'errors', '');

                $content['original']['response'] = $message;
                $this->dispatchBookingError(
                    $inspector,
                    $message,
                    $content
                );

                return ['error' => $message];
            }

            if (isset($responseData['errors'])) {
                $errorDetails = json_encode($responseData['errors']);
                Log::error('Oracle API error (Booking Sync): '.$errorDetails);

                $content['original']['response'] = $errorDetails;
                $this->dispatchBookingError(
                    $inspector,
                    'Oracle API error (Room '.$roomIndex.'): '.$errorDetails,
                    $content
                );

                return ['error' => $responseData['errors']];
            }

            return ['response' => $responseData];

        } catch (ConnectException $e) {
            Log::error('Oracle Connection timeout (Booking Sync): '.$e->getMessage());

            $content['original']['response'] = $e->getMessage();
            $this->dispatchBookingError(
                $inspector,
                'Connection timeout',
                $content
            );

            return ['error' => 'Connection timeout'];
        } catch (ServerException $e) {
            Log::error('Oracle Server error (Booking Sync): '.$e->getMessage());

            $this->handleExceptionResponse($e, $content);
            $this->dispatchBookingError(
                $inspector,
                'Oracle Server error',
                $content
            );

            return ['error' => 'Server error'];
        } catch (Throwable $e) {
            Log::error('Oracle Unexpected error (Booking Sync): '.$e->getMessage());

            $this->handleExceptionResponse($e, $content);
            $this->dispatchBookingError(
                $inspector,
                'Unexpected error: '.$e->getMessage(),
                $content
            );

            return ['error' => $e->getMessage()];
        }
    }

    protected function handleExceptionResponse(Throwable $e, array &$content): void
    {
        if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
            $content['original']['response'] = json_decode($e->getResponse()->getBody()->getContents(), true);
        } else {
            $content['original']['response'] = $e->getMessage();
        }
    }

    protected function mapSingleRoomToOracleQuery(array $roomConfig): array
    {
        $totalAdults = Arr::get($roomConfig, 'adults', 0);
        $childrenAges = Arr::get($roomConfig, 'children_ages', []);
        $totalChildren = count($childrenAges);

        $roomType = Arr::get($roomConfig, 'room_type', null) ?? Arr::get($roomConfig, 'room_code', null);
        $ratePlanCode = Arr::get($roomConfig, 'rate_plan_code', null) ?? Arr::get($roomConfig, 'rate_code', null);

        return [
            'adults' => $totalAdults,
            'children' => $totalChildren,
            'childAge' => implode(',', $childrenAges),
            'roomType' => $roomType,
            'ratePlanCode' => $ratePlanCode,
        ];
    }

    /**
     * Helper method for mapping Occupancy filters to Oracle query parameters.
     * Oracle API in the example uses common 'adults', 'children', 'roomStayQuantity'.
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
     * Helper method for sending SaveSearchInspector on error.
     */
    protected function dispatchSearchError(array $searchInspector, string $message, string $hotelId, array $original = []): void
    {
        $parent_search_id = $searchInspector['search_id'];
        $searchInspector['search_id'] = Str::uuid();
        $original = array_merge($original, ['hotelId' => $hotelId]);

        SaveSearchInspector::dispatch(
            $searchInspector,
            $original,
            [],
            [],
            'error',
            ['side' => 'supplier', 'message' => $message, 'parent_search_id' => $parent_search_id]
        );
    }

    protected function dispatchBookingError($inspector, string $message, array $content): void
    {
        Log::warning('DISPATCHING Booking Error: '.$message);

        $errorPayload = [
            'side' => 'supplier',
            'message' => $message,
        ];

        SaveBookingInspector::dispatch(
            inspector: $inspector,
            content: $content,
            client_content: [],
            status: 'error',
            status_describe: $errorPayload
        );
    }

    /**
     * Retrieves a list of room classes for the hotel.
     *
     * @param  string  $hotelId  Hotel code.
     * @param  array  $inspector  Metadata for logging (Inspector).
     * @return array|null API response as an array or null on error.
     */
    public function getRoomClasses(string $hotelId, array $inspector = []): ?array
    {
        try {
            $this->ensureToken();
        } catch (Exception $e) {
            if (! empty($inspector)) {
                $this->dispatchSearchError(
                    $inspector,
                    $e->getMessage(),
                    $hotelId,
                    $this->lastTokenError ?: ['error' => $e->getMessage()]
                );
            }

            return ['error' => $e->getMessage(), 'token_error' => $this->lastTokenError];
        }

        $endpoint = "/rm/config/v1/hotels/{$hotelId}/roomClasses";

        $url = $this->credentials->baseUrl.$endpoint;
        $headers = $this->headers;
        $headers['x-hotelid'] = $hotelId;

        $requestMeta = [
            'hotelId' => $hotelId,
            'url' => $url,
            'headers' => $headers,
            'method' => 'GET',
            'body' => null,
        ];

        // 2. Determining the API callback for the request (GET)
        $apiCall = function () use ($url, $headers) {
            return $this->client->get($url, [
                'headers' => $headers,
                'timeout' => config('services.oracle.timeout', 60),
            ]);
        };

        // 3. Выполнение синхронного запроса
        $result = $this->executeSyncRequest(
            $apiCall,
            $inspector,
            $requestMeta,
            null
        );

        return $result['response'] ?? null;
    }

    /**
     * Retrieves a list of physical rooms for the hotel.
     *
     * @param  string  $hotelId  Hotel code.
     * @param  array  $inspector  Metadata for logging (Inspector).
     * @return array|null API response as an array or null on error.
     */
    public function getRooms(string $hotelId, array $inspector = []): ?array
    {
        try {
            $this->ensureToken();
        } catch (Exception $e) {
            if (! empty($inspector)) {
                $this->dispatchSearchError(
                    $inspector,
                    $e->getMessage(),
                    $hotelId,
                    $this->lastTokenError ?: ['error' => $e->getMessage()]
                );
            }

            return ['error' => $e->getMessage(), 'token_error' => $this->lastTokenError];
        }

        $endpoint = "/rm/config/v1/hotels/{$hotelId}/rooms";

        $url = $this->credentials->baseUrl.$endpoint;
        $headers = $this->headers;
        $headers['x-hotelid'] = $hotelId;

        $requestMeta = [
            'hotelId' => $hotelId,
            'url' => $url,
            'headers' => $headers,
            'method' => 'GET',
            'body' => null,
        ];

        // 2. Determining the API callback for the request (GET)
        $apiCall = function () use ($url, $headers) {
            return $this->client->get($url, [
                'headers' => $headers,
                'timeout' => config('services.oracle.timeout', 60),
            ]);
        };

        // 3. Выполнение синхронного запроса
        $result = $this->executeSyncRequest(
            $apiCall,
            $inspector,
            $requestMeta,
            null
        );

        return $result['response'] ?? null;
    }

    /**
     * Retrieves a list of room types for the hotel with filters.
     * Request parameters: physical=true&pseudo=true&summaryInfo=false
     *
     * @param  string  $hotelId  Hotel code.
     * @param  array  $inspector  Metadata for logging (Inspector).
     * @return array|null API response as an array or null on error.
     */
    public function getRoomTypes(string $hotelId, array $inspector = []): ?array
    {
        try {
            $this->ensureToken();
        } catch (Exception $e) {
            if (! empty($inspector)) {
                $this->dispatchSearchError(
                    $inspector,
                    $e->getMessage(),
                    $hotelId,
                    $this->lastTokenError ?: ['error' => $e->getMessage()]
                );
            }

            return ['error' => $e->getMessage(), 'token_error' => $this->lastTokenError];
        }

        // Parameters specified in the request
        $queryParams = http_build_query([
            'physical' => 'true',
            'pseudo' => 'true',
            'summaryInfo' => 'false',
        ]);

        $endpoint = "/rm/config/v1/hotels/{$hotelId}/roomTypes?".$queryParams;

        $url = $this->credentials->baseUrl.$endpoint;
        $headers = $this->headers;
        $headers['x-hotelid'] = $hotelId;

        $requestMeta = [
            'hotelId' => $hotelId,
            'url' => $url,
            'headers' => $headers,
            'method' => 'GET',
            'body' => null,
        ];

        // 2. Determining the API callback for the request (GET)
        $apiCall = function () use ($url, $headers) {
            return $this->client->get($url, [
                'headers' => $headers,
                'timeout' => config('services.oracle.timeout', 60),
            ]);
        };

        // 3. Выполнение синхронного запроса
        $result = $this->executeSyncRequest(
            $apiCall,
            $inspector,
            $requestMeta,
            null
        );

        return $result['response'] ?? null;
    }
}
