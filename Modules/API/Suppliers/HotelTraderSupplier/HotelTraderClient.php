<?php

namespace Modules\API\Suppliers\HotelTraderSupplier;

use App\Models\ApiBookingsMetadata;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use Exception;
use Fiber;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
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
            'Authorization' => 'Basic '.$authString,
        ];
    }


    public function getPriceByPropertyIds(array $hotelIds, array $filters, array $searchInspector): ?array
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


    public function book($filters, $inspectorBook)
    {
        $passengersData = ApiBookingInspectorRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $flatGuests = array_merge(...$guests);

        $mappedGuests = array_map(function ($guest) {
            return [
                'firstName' => $guest['given_name'],
                'lastName' => $guest['family_name'],
                'email' => 'test@hoteltrader.com', // or $guest['email'] if available
                'adult' => true, // or derive from age/title if needed
                'age' => $guest['age'] ?? 30,
                'phone' => '1234567890', // or $guest['phone'] if available
                'primary' => true, // or set logic if needed
            ];
        }, $flatGuests);

        $request = [
            'query' => $this->makeBookQueryString(),
            'variables' => $this->makeBookVariables($filters, $mappedGuests),
        ];

        $response = $this->executeGraphQlRequest(
            $this->credentials->graphqlBookUrl,
            $request,
            $inspectorBook
        );

        return [
            'request' => $request,
            'response' => Arr::get($response, 'data.book', []),
            'main_guest' => $mappedGuests,
            'errors' => Arr::get($response, 'errors', []),
        ];
    }

    public function cancel(ApiBookingsMetadata $apiBookingsMetadata, $inspectorBook)
    {
        $request = [
            'query' => $this->makeCancelQueryString(),
            'variables' => $this->makeCncelVariables($apiBookingsMetadata),
        ];

        $response = $this->executeGraphQlRequest(
            $this->credentials->graphqlBookUrl,
            $request,
            $inspectorBook
        );

        return [
            'request' => $request,
            'response' => Arr::get($response, 'data.cancel', []),
            'errors' => Arr::get($response, 'errors', []),
        ];
    }

    public function retrieve(ApiBookingsMetadata $apiBookingsMetadata, $inspectorBook)
    {
        $request = [
            'query' => $this->makeRetrieveQueryString(),
            'variables' => $this->makeRetrieveVariables($apiBookingsMetadata),
        ];

        $response = $this->executeGraphQlRequest(
            $this->credentials->graphqlBookUrl,
            $request,
            $inspectorBook
        );

        return [
            'request' => $request,
            'response' => Arr::get($response, 'data.getReservation', []),
            'errors' => Arr::get($response, 'errors', []),
        ];
    }


    public function availability(array $hotelIds, array $filters, array $searchInspector): ?array
    {
        return null;
    }

    public function modifyBooking(array $modifyData, array $inspector): ?array
    {
        return null;
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


    protected function makeBookVariables(array $filters, array $mappedGuests): array
    {
        $bookingItemData = ApiBookingItemRepository::getItemData($filters['booking_item']);

        return [
            'Book' => [
                'clientConfirmationCode' => $filters['booking_item'],
                'otaConfirmationCode' => $filters['booking_item'],
                'otaClientName' => 'htrader',
                'paymentInformation' => null,
                'rooms' => [
                    [
                        'htIdentifier' => Arr::get($bookingItemData, 'htIdentifier', []),
                        'clientRoomConfirmationCode' => $filters['booking_item'].'-1',
                        'roomSpecialRequests' => ['room test comment'],
                        'rates' => Arr::get($bookingItemData, 'rate', []),
                        'occupancy' => [
                            'guestAges' => '30,30',
                        ],
                        'guests' => $mappedGuests,
                    ],
                ],
            ],
        ];

        //        [
        //                    {
        //                        "firstName": "test",
        //                        "lastName": "booking",
        //                        "email": "test@hoteltrader.com",
        //                        "adult": true,
        //                        "age": 30,
        //                        "phone": "1234567890",
        //                        "primary": true
        //                    },
        //                    {
        //                        "firstName": "test1",
        //                        "lastName": "booking1",
        //                        "email": "test@hoteltrader.com",
        //                        "adult": false,
        //                        "age": 5,
        //                        "phone": "1234567890",
        //                        "primary": true
        //                    }
        //                ]
    }

    protected function makeBookQueryString(): string
    {
        return <<<'QUERY'
            mutation book($Book: BookRequestInput) {
              book(bookRequest: $Book) {
                htConfirmationCode
                clientConfirmationCode
                otaConfirmationCode
                consolidatedComments
                consolidatedHTMLComments
                bookingDate
                specialRequests
                propertyDetails {
                  ...propertyDetails
                }
                rooms {
                  ...roomDetails
                }
              }
            }

            fragment addressDetails on Address {
              address1
              address2
              cityName
              countryCode
              stateName
              zipCode
            }

            fragment propertyDetails on PropertyResponseEntity {
              address {
                ...addressDetails
              }
              checkInTime
              checkOutTime
              city
              hotelImageUrl
              latitude
              longitude
              propertyId
              propertyName
              starRating
              checkInPolicy
              minAdultAgeForCheckIn
            }

            fragment roomDetails on RoomResponse {
              cancellationDate
              cancellationFee
              cancelled
              cancellationPolicies {
                ...cancelPolicyDetails
              }
              checkInDate
              checkOutDate
              clientRoomConfirmationCode
              htRoomConfirmationCode
              crsConfirmationCode
              crsCancelConfirmationCode
              pmsConfirmationCode
              refundable
              roomName
              rateplanTag
              mealplanOptions {
                mealplanDescription
                mealplanCode
                mealplanName
              }
              rates {
                ...ratesDetails
              }
              occupancy {
                guestAges
              }
              guests {
                ...guestDetails
              }
              roomSpecialRequests
            }
            fragment cancelPolicyDetails on HtCancellationPolicy {
              startWindowTime
              endWindowTime
              currency
              cancellationCharge
              timeZone
              timeZoneUTC
            }
            fragment ratesDetails on RoomRatesResponseEntity {
              bar
              binding
              commissionable
              commissionAmount
              currencyCode
              netPrice
              tax
              grossPrice
              dailyPrice
              dailyTax
              payAtProperty
              aggregateTaxInfo {
                payAtBooking {
                  description
                  name
                  currency
                  value
                }
                payAtProperty {
                  description
                  name
                  currency
                  value
                }
              }
            }
            fragment guestDetails on RoomGuestResponseEntity {
              adult
              age
              email
              firstName
              lastName
              phone
              primary
            }
        QUERY;
    }


    protected function makeCncelVariables(ApiBookingsMetadata $apiBookingsMetadata): array
    {
        return [
            'Cancel' => [
                'htConfirmationCode' => $apiBookingsMetadata->supplier_booking_item_id,
            ],
        ];
    }

    protected function makeCancelQueryString(): string
    {
        return <<<'QUERY'
            mutation cancel($Cancel: CancelRequestInput) {
              cancel(cancelRequest: $Cancel) {
                htConfirmationCode
                clientConfirmationCode
                allRoomsCancelled
                rooms {
                  htRoomConfirmationCode
                  clientRoomConfirmationCode
                  cancelled
                  currency
                  cancellationDate
                }
              }
            }
        QUERY;
    }


    protected function makeRetrieveVariables(ApiBookingsMetadata $apiBookingsMetadata): array
    {
        return [
            'GetReservation' => [
                'htConfirmationCode' => $apiBookingsMetadata->supplier_booking_item_id,
            ],
        ];
    }

    protected function makeRetrieveQueryString(): string
    {
        return <<<'QUERY'
            query getReservation($GetReservation: GetReservationRequestInput) {
              getReservation(getReservationRequest: $GetReservation) {
                htConfirmationCode
                clientConfirmationCode
                otaConfirmationCode
                otaClientName
                consolidatedComments
                consolidatedHTMLComments
                bookingDate
                membershipId
                specialRequests
                aggregateGrossPrice
                aggregateNetPrice
                aggregateTax
                aggregatePayAtProperty
                aggregateCancellationFee
                propertyDetails {
                  ...propertyDetails
                    }
                rooms {
                  ...roomDetails
                    }
                }
            }
            fragment addressDetails on Address {
              address1
              address2
              cityName
              countryCode
              stateName
              zipCode
            }
            fragment propertyDetails on PropertyResponseEntity {
              address {
                ...addressDetails
                }
              checkInTime
              checkOutTime
              city
              hotelImageUrl
              latitude
              longitude
              propertyId
              propertyName
              starRating
              checkInPolicy
              minAdultAgeForCheckIn
            }
            fragment roomDetails on RoomResponse {
              cancellationDate
              cancellationFee
              cancelled
              cancellationPolicies {
                ...cancelPolicyDetails
                }
              checkInDate
              checkOutDate
              clientRoomConfirmationCode
              htRoomConfirmationCode
              crsConfirmationCode
              crsCancelConfirmationCode
              pmsConfirmationCode
              refundable
              rateplanTag
              roomName
              mealplanOptions {
                mealplanDescription
                mealplanCode
                mealplanName
                }
              rates {
                ...ratesDetails
                }
              occupancy {
                guestAges
                }
              guests {
                ...guestDetails
                }
              roomSpecialRequests
            }
            fragment cancelPolicyDetails on HtCancellationPolicy {
              startWindowTime
              endWindowTime
              currency
              cancellationCharge
              timeZone
              timeZoneUTC
            }
            fragment ratesDetails on RoomRatesResponseEntity {
              bar
              binding
              commissionable
              commissionAmount
              currency
              netPrice
              tax
              grossPrice
              dailyPrice
              dailyTax
              payAtProperty
              aggregateTaxInfo {
                payAtBooking {
                  description
                  name
                  currency
                  value
                    }
                payAtProperty {
                  description
                  name
                  currency
                  value
                    }
                }
            }
            fragment guestDetails on RoomGuestResponseEntity {
              adult
              age
              email
              firstName
              lastName
              phone
              primary
            }
        QUERY;
    }

    /**
     * Generic method to execute a GraphQL request.
     *
     * @param  string  $endpointUrl  The specific GraphQL endpoint to use.
     * @param  array  $payload  The GraphQL request payload (query, variables, operationName).
     * @return array|null The JSON decoded response data, or null on error.
     *
     * @throws GuzzleException
     */
    protected function executeGraphQlRequest(string $endpointUrl, array $payload, array $inspectorBook): ?array
    {
        //        try {
        $client = new Client;
        $response = $client->post($endpointUrl, [
            'headers' => $this->headers,
            'json' => $payload,
            'timeout' => config('services.hotel_trader.timeout', 60),
        ]);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            Log::error('HotelTrader GraphQL HTTP Error: '.$response->getStatusCode().' for '.$endpointUrl);
            throw new Exception('HotelTrader GraphQL HTTP Error: '.$response->getStatusCode());
        }

        return json_decode($response->getBody()->getContents(), true);
        //        } catch (Exception $e) {
        //            Log::error('HotelTrader GraphQL Client Exception: '.$e->getMessage());
        //
        //            return null;
        //        }
    }


    // ####### Search API Methods only test console comand ########

    /**
     * Sends a GraphQL query to the HotelTrader Search API.
     *
     * @param  array  $variables  Optional variables for the query.
     * @param  string|null  $query  The GraphQL query string.
     * @param  string|null  $operationName  Optional operation name for the query.
     * @return array|null The JSON decoded response data, or null on error.
     *
     * @throws Exception
     */
    public function sendSearchQueryTest(array $variables = [], ?string $query = null, ?string $operationName = null): ?array
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
}
