<?php

namespace Modules\API\Suppliers\HotelTrader\Client;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveSearchInspector;
use App\Models\ApiBookingsMetadata;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use Exception;
use Fiber;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

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

            $original = ['HotelTrader' => ['request' => $responseData]];

            if (isset($responseData['errors'])) {
                Log::error('HotelTrader GraphQL Application Error: '.json_encode($responseData['errors']));

                return ['error' => $responseData['errors']];
            }

            $res = $responseData['data']['getPropertiesByIds']['properties'] ?? null;

            return [
                'request' => $rq,
                'response' => $res,
            ];
        } catch (ConnectException $e) {
            Log::error('Connection timeout: '.$e->getMessage());
            $parent_search_id = $searchInspector['search_id'];
            $searchInspector['search_id'] = Str::uuid();
            SaveSearchInspector::dispatch($searchInspector, $original, [], [], 'error',
                ['side' => 'supplier', 'message' => 'Connection timeout', 'parent_search_id' => $parent_search_id]);

            return ['error' => 'Connection timeout'];
        } catch (ServerException $e) {
            Log::error('Server error: '.$e->getMessage());
            $parent_search_id = $searchInspector['search_id'];
            $searchInspector['search_id'] = Str::uuid();
            SaveSearchInspector::dispatch($searchInspector, $original, [], [], 'error',
                ['side' => 'supplier', 'message' => 'HotelTrader Server error', 'parent_search_id' => $parent_search_id]);

            return ['error' => 'Server error'];
        } catch (Throwable $e) {
            Log::error('Unexpected error: '.$e->getMessage());
            $parent_search_id = $searchInspector['search_id'];
            $searchInspector['search_id'] = Str::uuid();
            SaveSearchInspector::dispatch($searchInspector, $original, [], [], 'error',
                ['side' => 'supplier', 'message' => 'Unexpected error', 'parent_search_id' => $parent_search_id]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @throws GuzzleException
     */
    public function book($filters, $inspectorBook): array
    {
        $passengersData = ApiBookingInspectorRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $bookingContactEmail = Arr::get($filters, 'booking_contact.email');
        $bookingContactPhone = Arr::get($filters, 'booking_contact.phone.number');

        $mappedGuests = $this->mapGuests($guests, $bookingContactEmail, $bookingContactPhone);

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

    /**
     * @throws GuzzleException
     */
    public function availability(array $hotelIds, array $filters, array $inspector): array
    {
        $request = [
            'query' => $this->makeSearchQueryString(),
            'variables' => $this->makeSearchVariables($filters, $hotelIds),
            'operationName' => 'getPropertiesByIds',
        ];

        $response = $this->executeGraphQlRequest(
            $this->credentials->graphqlSearchUrl,
            $request,
            $inspector
        );

        $rq = [
            'url' => $this->credentials->graphqlSearchUrl,
            'method' => 'POST',
            'headers' => $this->headers,
            'payload' => $request,
        ];

        return [
            'request' => $rq,
            'response' => Arr::get($response, 'data.getPropertiesByIds.properties', []),
            'errors' => Arr::get($response, 'errors', []),
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function modifyBooking(array $filters, array $inspector): ?array
    {
        $passengersData = Arr::get($filters, 'passengers');
        $guests = [];
        foreach ($passengersData as $passenger) {
            $room = $passenger['room'];
            if (! isset($guests[$room - 1])) {
                $guests[$room - 1] = [];
            }
            $guests[$room - 1][] = $passenger;
        }

        $bookIdData = ApiBookingInspectorRepository::bookedItem($filters['booking_id'], $filters['booking_item']);
        $bookIdDataRs = json_decode($bookIdData->request, true);

        $bookingContactEmail = Arr::get($bookIdDataRs, 'booking_contact.email');
        $bookingContactPhone = Arr::get($bookIdDataRs, 'booking_contact.phone.number');

        $mappedGuests = $this->mapGuests($guests, $bookingContactEmail, $bookingContactPhone);

        $request = [
            'query' => $this->makeModifyQueryString(),
            'variables' => $this->makeModifyVariables($filters, $mappedGuests),
            'operationName' => 'modify',
        ];

        $response = $this->executeGraphQlRequest(
            $this->credentials->graphqlBookUrl,
            $request,
            $inspector
        );

        return [
            'request' => $request,
            'response' => Arr::get($response, 'data.modify', []),
            'errors' => Arr::get($response, 'errors', []),
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function cancel(ApiBookingsMetadata $apiBookingsMetadata, $inspectorBook): array
    {
        $request = [
            'query' => $this->makeCancelQueryString(),
            'variables' => $this->makeCancelVariables($apiBookingsMetadata),
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

    /**
     * @throws GuzzleException
     */
    public function retrieve(ApiBookingsMetadata $apiBookingsMetadata, $inspectorBook): array
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

    protected function makeBookVariables(array $filters, array $mappedGuests): array
    {
        $roomSpecialRequests = $this->getRoomSpecialRequests($filters, $filters['booking_item']);
        $rooms = $this->buildRoomsArray($mappedGuests, $roomSpecialRequests, $filters['booking_item']);

        return [
            'Book' => [
                'clientConfirmationCode' => $filters['booking_item'],
                'otaConfirmationCode' => $filters['booking_item'],
                'otaClientName' => 'Fora',
                'paymentInformation' => null,
                'rooms' => $rooms,
            ],
        ];
    }

    protected function makeModifyQueryString(): string
    {
        return <<<'GRAPHQL'
        mutation modify($Modify: ModifyRequestInput) {
            modify(modifyRequest: $Modify) {
                htConfirmationCode
                clientConfirmationCode
                otaConfirmationCode
                otaClientName
                consolidatedComments
                consolidatedHTMLComments
                bookingDate
                aggregateTax
                membershipId
                specialRequests
                aggregateGrossPrice
                aggregateNetPrice
                aggregateTax
                aggregatePayAtProperty
                aggregateCancellationFee
                propertyDetails {
                    propertyId
                    propertyName
                    address {
                        address1
                        address2
                        cityName
                        countryCode
                        stateName
                        zipCode
                    }
                    checkInTime
                    checkOutTime
                    city
                    hotelImageUrl
                    latitude
                    longitude
                    starRating
                    checkInPolicy
                    minAdultAgeForCheckIn
                    timeZone
                    shortDescription
                    longDescription
                }
                rooms {
                    cancellationDate
                    cancellationFee
                    cancelled
                    cancellationPolicies {
                        startWindowTime
                        endWindowTime
                        currency
                        cancellationCharge
                        timeZone
                        timeZoneUTC
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
                    rateplanCode
                    shortDescription
                    longDescription
                    mealplanOptions {
                        breakfastIncluded
                        lunchIncluded
                        dinnerIncluded
                        allInclusive
                        mealplanName
                        mealplanCode
                        mealplanDescription
                    }
                    rates {
                        bar
                        binding
                        commissionable
                        commissionAmount
                        netPrice
                        tax
                        currency
                        grossPrice
                        dailyPrice
                        dailyTax
                        payAtProperty
                        taxInfo {
                            payAtBooking {
                                date
                                description
                                name
                                currency
                                value
                            }
                            payAtProperty {
                                date
                                description
                                name
                                currency
                                value
                            }
                        }
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
                    occupancy {
                        noOfAdults
                        noOfChildren
                        childrenAges
                    }
                    guests {
                        adult
                        age
                        email
                        firstName
                        lastName
                        phone
                        primary
                    }
                    roomSpecialRequests
                }
            }
        }
    GRAPHQL;
    }

    protected function makeModifyVariables(array $filters, array $mappedGuests): array
    {
        $isSoftChange = ! Arr::get($filters, 'new_booking_item');

        $meta = ApiBookingsMetadataRepository::getBookedItem($filters['booking_id'], $filters['booking_item']);
        $htCode = $meta->supplier_booking_item_id;
        $roomSpecialRequests = $this->getRoomSpecialRequests($filters, $filters['booking_item']);

        if ($isSoftChange) {
            $rooms = $this->buildRoomsArray($mappedGuests, $roomSpecialRequests, $filters['booking_item'], 'modify');
            foreach ($rooms as &$room) {
                unset($room['occupancy'], $room['rates']);
            }
        } else {
            $rooms = $this->buildRoomsArray($mappedGuests, $roomSpecialRequests, $filters['new_booking_item'], 'modify');
        }

        $k = 1;
        foreach ($rooms as &$room) {
            $room['htRoomConfirmationCode'] = $htCode.'-'.$k;
            $room['status'] = 'MODIFY';
            $k++;
        }

        //        dd($isSoftChange, $htCode, $filters['booking_item'], $rooms);

        return [
            'Modify' => [
                'htConfirmationCode' => $htCode,
                'clientConfirmationCode' => $filters['booking_item'],
                'otaConfirmationCode' => $filters['booking_item'],
                'otaClientName' => 'Fora',
                'rooms' => $rooms,
            ],
        ];
    }

    protected function getRoomSpecialRequests(array $filters, string $bookingItem): array
    {
        $result = [];
        $specialRequests = Arr::get($filters, 'special_requests', []);
        foreach ($specialRequests as $request) {
            if ($request['booking_item'] === $bookingItem) {
                $room = $request['room'];
                if (! isset($result[$room - 1])) {
                    $result[$room - 1] = [];
                }
                $result[$room - 1][] = $request['special_request'];
            }
        }

        return $result;
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

    protected function makeCancelVariables(ApiBookingsMetadata $apiBookingsMetadata): array
    {
        return [
            'Cancel' => [
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

    protected function makeRetrieveVariables(ApiBookingsMetadata $apiBookingsMetadata): array
    {
        return [
            'GetReservation' => [
                'htConfirmationCode' => $apiBookingsMetadata->supplier_booking_item_id,
            ],
        ];
    }

    protected function buildRoomsArray(array $mappedGuests, array $roomSpecialRequests, string $bookingItem, string $mode = 'book'): array
    {
        $rooms = [];
        $childrenBookingItems = ApiBookingItemRepository::getChildrenBookingItems($bookingItem);

        if ($childrenBookingItems) {
            $roomNumber = 1;
            foreach ($childrenBookingItems as $k => $childBookingItem) {
                $childBookingItemData = ApiBookingItemRepository::getItemData($childBookingItem);
                $guestAges = implode(',', array_column($mappedGuests[$k], 'age'));
                $room = [
                    'clientRoomConfirmationCode' => $childBookingItem.'-'.$roomNumber,
                    'rates' => Arr::get($childBookingItemData, 'rate', []),
                    'occupancy' => [
                        'guestAges' => $guestAges,
                    ],
                    'guests' => $mappedGuests[$k] ?? [],
                ];
                if ($mode === 'book') {
                    $room['htIdentifier'] = Arr::get($childBookingItemData, 'htIdentifier', []);
                }
                if (isset($roomSpecialRequests[$k])) {
                    $room['roomSpecialRequests'] = $roomSpecialRequests[$k];
                }
                $rooms[] = $room;
                $roomNumber++;
            }
        } else {
            $bookingItemData = ApiBookingItemRepository::getItemData($bookingItem);
            $guestAges = implode(',', array_column($mappedGuests[0], 'age'));
            $room = [
                'clientRoomConfirmationCode' => $bookingItem.'-1',
                'rates' => Arr::get($bookingItemData, 'rate', []),
                'occupancy' => [
                    'guestAges' => $guestAges,
                ],
                'guests' => $mappedGuests[0] ?? [],
            ];
            if ($mode === 'book') {
                $room['htIdentifier'] = Arr::get($bookingItemData, 'htIdentifier', []);
            }
            if (isset($roomSpecialRequests[0])) {
                $room['roomSpecialRequests'] = $roomSpecialRequests[0];
            }
            $rooms[] = $room;
        }

        return $rooms;
    }

    protected function mapGuests(array $guests, ?string $сontactEmail, ?string $сontactPhone): array
    {
        return array_map(function ($roomGuests) use ($сontactEmail, $сontactPhone) {
            $result = [];
            foreach ($roomGuests as $i => $guest) {
                $result[] = [
                    'firstName' => Arr::get($guest, 'given_name', ''),
                    'lastName' => Arr::get($guest, 'family_name', ''),
                    'email' => Arr::get($guest, 'email') ?? $сontactEmail ?? 'test@hoteltrader.com',
                    'adult' => (Arr::get($guest, 'age') ?? 30) >= 18,
                    'age' => Arr::get($guest, 'age') ?? 30,
                    'phone' => Arr::get($guest, 'phone') ?? $сontactPhone ?? '1234567890',
                    'primary' => $i === 0,
                ];
            }

            return $result;
        }, $guests);
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
        $content['original']['request'] = $payload;
        $content['original']['response'] = '';

        try {
            // Imitation error 500
            // Uncomment the next line to simulate a server error for testing purposes
            //            throw new \GuzzleHttp\Exception\ServerException(
            //                'Server error',
            //                new \GuzzleHttp\Psr7\Request('POST', 'test'),
            //                new \GuzzleHttp\Psr7\Response(500)
            //            );
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

            $rs = $response->getBody()->getContents();
            $content['original']['response'] = $rs;

            if (str_contains($rs, 'errors')) {
                SaveBookingInspector::dispatch($inspectorBook, $content, [], 'error', ['side' => 'app', 'message' => $rs]);
            }

            return json_decode($rs, true);
        } catch (ConnectException $e) {
            Log::error('HotelTrader GraphQL Client Exception: '.$e->getMessage());
            // Timeout
            Log::error('HotelTrader _ Connection timeout: '.$e->getMessage());
            SaveBookingInspector::dispatch($inspectorBook, $content, [], 'error', ['side' => 'supplier', 'message' => 'Connection timeout']);

            return ['error' => 'HotelTrader Connection timeout'];
        } catch (ServerException $e) {
            // Error 500
            Log::error('HotelTrader _ Server error: '.$e->getMessage());
            SaveBookingInspector::dispatch($inspectorBook, $content, [], 'error', ['side' => 'supplier', 'message' => 'Server error']);

            return ['error' => 'HotelTrader Server error'];
        } catch (Exception $e) {
            Log::error('HotelTrader _ Unexpected error: '.$e->getMessage());
            SaveBookingInspector::dispatch($inspectorBook, $content, [], 'error', ['side' => 'supplier', 'message' => $e->getMessage()]);

            return ['error' => $e->getMessage()];
        }
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
     * @throws Exception|GuzzleException
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
