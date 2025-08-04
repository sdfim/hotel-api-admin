<?php

namespace Modules\API\Suppliers\HotelTraderSupplier;

use Exception;
use Fiber;
use GuzzleHttp\Client;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HotelTraderClient
{
    protected Credentials $credentials;

    // We no longer need a single $baseUrl property, as it will be dynamic
    // protected string $baseUrl;

    protected array $headers;

    public function __construct(
        private readonly Client $client,
    ) {
        $this->credentials = CredentialsFactory::fromConfig();
        $authString = base64_encode($this->credentials->username.':'.$this->credentials->password);

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$authString,
        ];
    }

    /**
     * Helper to get the HTTP client instance with default headers.
     * The base URL will now be passed dynamically to the post method.
     *
     * @param  string  $endpointUrl  The specific GraphQL endpoint for the request.
     */
    protected function httpClient(string $endpointUrl): PendingRequest
    {
        return Http::withHeaders($this->headers)
            ->timeout(config('services.hotel_trader.timeout', 60));
    }

    protected function makeSearchVariables(array $filters, array $hotelIds): array
    {
        foreach ($filters['occupancy'] as $occupancy) {
            $guestAges = [];
            // Add adults (each adult is 33 years old)
            if (isset($occupancy['adults']) && is_numeric($occupancy['adults'])) {
                $guestAges = array_merge($guestAges, array_fill(0, $occupancy['adults'], 33));
            }
            // Add children ages
            if (isset($occupancy['children_ages']) && is_array($occupancy['children_ages'])) {
                $guestAges = array_merge($guestAges, $occupancy['children_ages']);
            }
            $occupancies[] = [
                'checkInDate' => $filters['checkin'],
                'checkOutDate' => $filters['checkout'],
                'guestAges' => implode(',', $guestAges),
            ];
        }

        return [
            'SearchCriteriaByIds' => [
                'propertyIds' => $hotelIds,
                'occupancies' => $occupancies,
            ],
        ];
    }

    protected function makeSearchQueryString(): string
    {
        return <<<'QUERY'
            query getPropertiesByIds($SearchCriteriaByIds: SearchCriteriaByIdsInput) {
                getPropertiesByIds(searchCriteriaByIds: $SearchCriteriaByIds) {
                    properties {
                        propertyId
                        propertyName
                        occupancies {
                            occupancyRefId
                            checkInDate
                            checkOutDate
                            guestAges
                        }
                        rooms {
                            occupancyRefId
                            htIdentifier
                            roomName
                            roomCode
                            rateplanTag
                            shortDescription
                            numRoomsAvail
                            longDescription
                            consolidatedComments
                            paymentType
                            rateInfo {
                                bar
                                binding
                                commissionable
                                commissionAmount
                                currency
                                netPrice
                                tax
                                grossPrice
                                payAtProperty
                                dailyPrice
                                dailyTax
                                aggregateTaxInfo {
                                    payAtBooking {
                                        name
                                        value
                                        currency
                                        description
                                    }
                                    payAtProperty {
                                        name
                                        currency
                                        value
                                    }
                                }
                                taxInfo {
                                    payAtBooking {
                                        date
                                        name
                                        currency
                                        description
                                        value
                                    }
                                    payAtProperty {
                                        date
                                        name
                                        currency
                                        description
                                        value
                                    }
                                }
                            }
                            mealplanOptions {
                                mealplanDescription
                                mealplanCode
                                mealplanName
                            }
                            refundable
                            cancellationPolicies {
                                startWindowTime
                                endWindowTime
                                cancellationCharge
                                currency
                                timeZone
                                timeZoneUTC
                            }
                        }
                        shortDescription
                        longDescription
                        city
                        latitude
                        longitude
                        starRating
                        hotelImageUrl
                    }
                }
            }
        QUERY;
    }

    public function getHbsiPriceByPropertyIds(array $hotelIds, array $filters, array $searchInspector): ?array
    {
        $payload = [
            'query' => $this->makeSearchQueryString(),
            'variables' => $this->makeSearchVariables($filters, $hotelIds),
            'operationName' => 'getPropertiesByIds',
        ];

        $client = new Client;
        $promise = $client->postAsync($this->credentials->graphqlSearchUrl, [
            'headers' => $this->headers,
            'json' => $payload,
            'timeout' => config('services.hotel_trader.timeout', 60),
        ]);

        try {
            $result = Fiber::suspend([$promise])[0];
            $body = $result->getBody()->getContents();
            $responseData = json_decode($body, true);

            $rq = [
                'url' => $this->credentials->graphqlSearchUrl,
                'method' => 'POST',
                'headers' => $this->headers,
                'payload' => $payload,
            ];

            if (isset($responseData['errors'])) {
                Log::error('HotelTrader GraphQL Application Error: '.json_encode($responseData['errors']));

                return ['error' => $responseData['errors']];
            }

            $res = $responseData['data']['getPropertiesByIds']['properties'] ?? null;

            return [
                'request' => $rq,
                'response' => $res,
            ];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Connection timeout: '.$e->getMessage());

            return ['error' => 'Connection timeout'];
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            Log::error('Server error: '.$e->getMessage());

            return ['error' => 'Server error'];
        } catch (\Throwable $e) {
            Log::error('Unexpected error: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Sends a GraphQL query to the HotelTrader Search API.
     *
     * @param  string  $query  The GraphQL query string.
     * @param  array  $variables  Optional variables for the query.
     * @param  string|null  $operationName  Optional operation name for the query.
     * @return array|null The JSON decoded response data, or null on error.
     *
     * @throws Exception
     */
    public function sendSearchQuery(array $variables = [], ?string $query = null, ?string $operationName = null): ?array
    {
        return $this->executeGraphQlRequest(
            $this->credentials->graphqlSearchUrl,
            [
                'query' => $query ?? <<<'QUERY'
            query getPropertiesByIds($SearchCriteriaByIds: SearchCriteriaByIdsInput) {
                getPropertiesByIds(searchCriteriaByIds: $SearchCriteriaByIds) {
                    properties {
                        propertyId
                        propertyName
                        occupancies {
                            occupancyRefId
                            checkInDate
                            checkOutDate
                            guestAges
                        }
                        rooms {
                            occupancyRefId
                            htIdentifier
                            roomName
                            roomCode
                            rateplanTag
                            shortDescription
                            numRoomsAvail
                            longDescription
                            consolidatedComments
                            paymentType
                            rateInfo {
                                bar
                                binding
                                commissionable
                                commissionAmount
                                currency
                                netPrice
                                tax
                                grossPrice
                                payAtProperty
                                dailyPrice
                                dailyTax
                                aggregateTaxInfo {
                                    payAtBooking {
                                        name
                                        value
                                        currency
                                        description
                                    }
                                    payAtProperty {
                                        name
                                        currency
                                        value
                                    }
                                }
                                taxInfo {
                                    payAtBooking {
                                        date
                                        name
                                        currency
                                        description
                                        value
                                    }
                                    payAtProperty {
                                        date
                                        name
                                        currency
                                        description
                                        value
                                    }
                                }
                            }
                            mealplanOptions {
                                mealplanDescription
                                mealplanCode
                                mealplanName
                            }
                            refundable
                            cancellationPolicies {
                                startWindowTime
                                endWindowTime
                                cancellationCharge
                                currency
                                timeZone
                                timeZoneUTC
                            }
                        }
                        shortDescription
                        longDescription
                        city
                        latitude
                        longitude
                        starRating
                        hotelImageUrl
                    }
                }
            }
        QUERY,
                'variables' => $variables,
                'operationName' => $operationName,
            ]
        );
    }

    /**
     * Sends a GraphQL mutation to the HotelTrader Book/Modify/Cancel API.
     *
     * @param  string  $mutation  The GraphQL mutation string.
     * @param  array  $variables  Optional variables for the mutation.
     * @param  string|null  $operationName  Optional operation name for the mutation.
     * @return array|null The JSON decoded response data, or null on error.
     *
     * @throws Exception
     */
    public function sendBookingMutation(string $mutation, array $variables = [], ?string $operationName = null): ?array
    {
        return $this->executeGraphQlRequest(
            $this->credentials->graphqlBookUrl,
            [
                'query' => $mutation,
                'variables' => $variables,
                'operationName' => $operationName,
            ]
        );
    }

    /**
     * Generic method to execute a GraphQL request.
     *
     * @param  string  $endpointUrl  The specific GraphQL endpoint to use.
     * @param  array  $payload  The GraphQL request payload (query, variables, operationName).
     * @return array|null The JSON decoded response data, or null on error.
     *
     * @throws Exception
     */
    protected function executeGraphQlRequest(string $endpointUrl, array $payload): ?array
    {
        try {
            $response = $this->httpClient($endpointUrl)->post($endpointUrl, $payload);

            if ($response->clientError() || $response->serverError()) {
                Log::error('HotelTrader GraphQL HTTP Error: '.$response->status().' - '.$response->body().' for '.$endpointUrl);
                throw new Exception('HotelTrader GraphQL HTTP Error: '.$response->status());
            }

            $responseData = $response->json();

            if (isset($responseData['errors'])) {
                Log::error('HotelTrader GraphQL Application Error: '.json_encode($responseData['errors']).' for '.$endpointUrl);
                throw new Exception('HotelTrader GraphQL Error: '.json_encode($responseData['errors']));
            }

            Log::info('-------------------------------------------- HotelTrader GraphQL REQUEST --------------------------------------------');
            Log::info('Endpoint: '.$endpointUrl);
            Log::info(json_encode($payload, JSON_PRETTY_PRINT));
            Log::info('-------------------------------------------- HotelTrader GraphQL RESPONSE --------------------------------------------');
            Log::info(json_encode($responseData, JSON_PRETTY_PRINT));

            return $responseData;

        } catch (Exception $e) {
            Log::error('HotelTrader GraphQL Client Exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Example method to fetch hotel availability using a GraphQL query.
     * This will now specifically use the search GraphQL endpoint.
     *
     * @param  array  $filters  Example: ['checkin' => 'YYYY-MM-DD', 'checkout' => 'YYYY-MM-DD', 'occupancy' => [...]]
     * @param  array  $searchInspector  Data for logging/inspection.
     *
     * @throws Exception
     */
    public function getHotelAvailability(array $hotelIds, array $filters, array $searchInspector): ?array
    {
        $query = '
            query GetHotelAvailability($hotelIds: [ID!]!, $checkin: String!, $checkout: String!, $occupancy: [OccupancyInput!]!) {
                hotels(ids: $hotelIds) {
                    id
                    name
                    availability(checkin: $checkin, checkout: $checkout, occupancy: $occupancy) {
                        roomType
                        price {
                            amount
                            currency
                        }
                        # ... other availability fields
                    }
                }
            }
        ';

        $variables = [
            'hotelIds' => $hotelIds,
            'checkin' => Arr::get($filters, 'checkin'),
            'checkout' => Arr::get($filters, 'checkout'),
            'occupancy' => Arr::get($filters, 'occupancy', []),
        ];

        // Call the specific search query sender
        $response = $this->sendSearchQuery($query, $variables, 'GetHotelAvailability');

        if (isset($response['data']['hotels'])) {
            return $response['data']['hotels'];
        }

        return null;
    }

    /**
     * Example method to create a booking using a GraphQL mutation.
     * This will now specifically use the booking GraphQL endpoint.
     *
     * @throws Exception
     */
    public function createBooking(array $bookingData, array $inspectorBook): ?array
    {
        $mutation = '
            mutation CreateBooking($input: CreateBookingInput!) {
                createBooking(input: $input) {
                    bookingId
                    status
                    # ... other booking confirmation details
                }
            }
        ';

        $variables = [
            'input' => $bookingData,
        ];

        // Call the specific booking mutation sender
        $response = $this->sendBookingMutation($mutation, $variables, 'CreateBooking');

        if (isset($response['data']['createBooking'])) {
            return $response['data']['createBooking'];
        }

        return null;
    }

    /**
     * Example method for a modify booking operation.
     * This will use the booking GraphQL endpoint.
     *
     * @throws Exception
     */
    public function modifyBooking(array $modifyData, array $inspector): ?array
    {
        // Example mutation structure for modify based on pullapi-graphql.json "4. Modifications"
        $mutation = '
            mutation ModifyBooking($input: ModifyBookingInput!) {
                modifyBooking(input: $input) {
                    bookingId
                    status
                    # ... relevant fields after modification
                }
            }
        ';

        $variables = [
            'input' => $modifyData,
        ];

        $response = $this->sendBookingMutation($mutation, $variables, 'ModifyBooking');

        if (isset($response['data']['modifyBooking'])) {
            return $response['data']['modifyBooking'];
        }

        return null;
    }

    /**
     * Example method for a cancel booking operation.
     * This will use the booking GraphQL endpoint.
     *
     * @throws Exception
     */
    public function cancelBooking(string $bookingId, array $inspectorCancel): ?array
    {
        // Example mutation structure for cancel based on pullapi-graphql.json
        $mutation = '
            mutation CancelBooking($bookingId: ID!) {
                cancelBooking(bookingId: $bookingId) {
                    bookingId
                    status
                    # ... relevant fields after cancellation
                }
            }
        ';

        $variables = [
            'bookingId' => $bookingId,
        ];

        $response = $this->sendBookingMutation($mutation, $variables, 'CancelBooking');

        if (isset($response['data']['cancelBooking'])) {
            return $response['data']['cancelBooking'];
        }

        return null;
    }
}
