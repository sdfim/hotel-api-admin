<?php

namespace Modules\API\Suppliers\HbsiSupplier;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiSearchInspectorRepository;
use App\Repositories\ConfigRepository;
use Exception;
use Fiber;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use Throwable;

class HbsiClient
{
    private const VERSION = '2006A';

    private const INTERFACE = 'HBSI XML 4 OTA';

    private string $requestId;

    private string $timeStamp;

    private string $componentInfoId;

    private array $mainGuest;

    /** @var Credentials */
    private Credentials $credentials;

    /**
     * @param Client $client
     * @param array $headers
     */
    public function __construct(
        private readonly Client $client = new Client(),
        private readonly array  $headers = [
            'Content-Type' => 'text/xml; charset=UTF8',
        ],
    )
    {
        $this->requestId = time() . '_tentravel';
        $this->timeStamp = date('Y-m-d\TH:i:sP');
        $this->credentials = CredentialsFactory::fromConfig();
    }

    /**
     * @throws GuzzleException
     * @throws Exception|Throwable
     */
    public function getHbsiPriceByPropertyIds(array $hotelIds, array $filters): ?array
    {
        $bodyQuery = $this->makeRequest($this->hotelAvailRQ($hotelIds, $filters), 'HotelAvailRQ');
        $promise = $this->client->requestAsync('POST', $this->credentials->searchBookUrl, [
            'headers' => $this->headers,
            'body' => $bodyQuery,
            'timeout' => ConfigRepository::getTimeout()
        ]);

        $result = Fiber::suspend($promise);

        $body = $result['value']->getBody()->getContents();

        return $this->processXmlBody($body, $bodyQuery);
    }

    /**
     * @param array $filters
     * @return array|null
     * @throws GuzzleException
     */
    public function handleBook(array $filters): ?array
    {
        $this->mainGuest = [];
        $hotelId = ApiBookingItemRepository::getHotelSupplierId($filters['booking_item']);
        $bodyQuery = $this->makeRequest($this->hotelResRQ($filters), 'HotelResRQ', $hotelId);
        $response = $this->sendRequest($bodyQuery);
        $body = $response->getBody()->getContents();

        return $this->processXmlBody($body, $bodyQuery, true);
    }

    /**
     * @param array $filters
     * @return array|null
     * @throws GuzzleException
     */
    public function modifyBook(array $filters): ?array
    {
        $this->mainGuest = [];
        $hotelId = ApiBookingItemRepository::getHotelSupplierId($filters['booking_item']);
        $bodyQuery = $this->makeRequest($this->hotelResRQ($filters), 'HotelResModifyRQ', $hotelId);
        $response = $this->sendRequest($bodyQuery);
        $body = $response->getBody()->getContents();

        return $this->processXmlBody($body, $bodyQuery, true);
    }

    /**
     * @param array $reservation
     * @param string|null $hotelId
     * @return array|null
     * @throws GuzzleException
     */
    public function retrieveBooking(array $reservation, ?string $hotelId = null): ?array
    {
        $bodyQuery = $this->makeRequest($this->readRQ($reservation), 'ReadRQ', $hotelId);
        $response = $this->sendRequest($bodyQuery);
        $body = $response->getBody();
        return $this->processXmlBody($body, $bodyQuery);
    }

    /**
     * @param array $reservation
     * @param string|null $hotelId
     * @return array|null
     * @throws GuzzleException
     */
    public function cancelBooking(array $reservation, ?string $hotelId = null): ?array
    {
        $bodyQuery = $this->makeRequest($this->cancelRQ($reservation), 'CancelRQ', $hotelId);
        $response = $this->sendRequest($bodyQuery);
        $body = $response->getBody();
        return $this->processXmlBody($body, $bodyQuery);
    }

    /**
     * @param $body
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function sendRequest($body): ResponseInterface
    {
        return $this->client->request('POST', $this->credentials->searchBookUrl, [
            'headers' => $this->headers,
            'body' => $body,
            'timeout' => ConfigRepository::getTimeout(),
        ]);
    }

    /**
     * @param object|string $body
     * @param string $bodyQuery
     * @param bool $addGuest
     * @return array|null
     */
    private function processXmlBody(object|string $body, string $bodyQuery, bool $addGuest = false): ?array
    {
        if ($this->isXml($body)) {
            try {
                $res = [
                    'request' => $bodyQuery,
                    'response' => new SimpleXMLElement(strval($body), LIBXML_NOCDATA)
                ];

                //TODO: REMOVE WHEN FINISHED TESTING WITH HBSI
                Log::info('-------------------------------------------- REQUEST --------------------------------------------');
                Log::info($res['request']);
                Log::info('-------------------------------------------- RESPONSE --------------------------------------------');
                Log::info($res['response']->asXML());
                //TODO: REMOVE WHEN FINISHED TESTING WITH HBSI

                if ($addGuest) $res['main_guest'] = json_encode($this->mainGuest);
                return $res;
            } catch (Exception $e) {
                Log::error('HbsiClient ' . $e->getMessage());
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param string $body
     * @param string $typeRequest
     * @param string|null $hotelId
     * @return string
     */
    private function makeRequest(string $body, string $typeRequest, ?string $hotelId = ''): string
    {
        if ($hotelId === '') $hotelId = $this->credentials->componentInfoId;
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="' . $this->credentials->channelIdentifierId . '" Version="' . self::VERSION . '" Interface="' . self::INTERFACE . '"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="' . $hotelId . '" User="' . $this->credentials->username . '" Pwd="' . $this->credentials->password . '" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="' . $this->requestId . '" Transaction="' . $typeRequest . '">
                    ' . $body . '
                </soap-env:Body>
            </soap-env:Envelope>';
    }

    /**
     * @param array $hotelIds
     * @param array $params
     * @return string
     */
    private function hotelAvailRQ(array $hotelIds, array $params = []): string
    {
        foreach ($hotelIds as $hotelId) {
            $hotelRefs[] = '<HotelRef HotelCode="' . $hotelId . '" />';
        }

        $start = $params['checkin'] ?? '2024-02-10';
        $end = $params['checkout'] ?? '2024-02-15';
        $currency = $params['currency'] ?? 'USD';

        $roomStayCandidates = $this->occupancyToXml($params['occupancy']);

        return '<OTA_HotelAvailRQ Target="'.$this->credentials->target.'" Version="1.003" TimeStamp="' . $this->timeStamp . '"
                xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                <POS>
                    <Source>
                        <RequestorID Type="18" ID="Partner"/>
                        <BookingChannel Type="2" Primary="true">
                            <CompanyName>HBSI</CompanyName>
                        </BookingChannel>
                    </Source>
                </POS>
                <AvailRequestSegments>
                    <AvailRequestSegment>
                        <HotelSearchCriteria>
                            <Criterion>
                                <StayDateRange Start="' . $start . '" Duration="Day" End="' . $end . '"></StayDateRange>
                                <RateRange RateTimeUnit="Day" CurrencyCode="' . $currency . '" ></RateRange>
                                <RatePlanCandidates>
                                    <RatePlanCandidate RatePlanCode="*" RPH="1">
                                        <HotelRefs>
                                            ' . implode('', $hotelRefs) . '
                                        </HotelRefs>
                                        <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                    </RatePlanCandidate>
                                </RatePlanCandidates>
                                ' . $roomStayCandidates . '
                            </Criterion>
                        </HotelSearchCriteria>
                    </AvailRequestSegment>
                </AvailRequestSegments>
            </OTA_HotelAvailRQ>';
    }

    /**
     * @param array $filters
     * @return string
     * @throws Exception
     */
    private function hotelResRQ(array $filters): string
    {
        $response = ApiSearchInspectorRepository::getResponse($filters['search_id']);
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingItemData = ApiBookingItemRepository::getItemData($filters['booking_item']);
        $passengersData = ApiBookingInspectorRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $roomStaysArr = $this->processRoomStaysArr($response, $bookingItemData, $filters, $guests);
        $resGuestsArr = $this->processResGuestsArr($guests, $filters);
        $resGlobalInfoArr = $this->processDepositPaymentsArr($filters, $roomStaysArr);

        $resGlobalInfo = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($resGlobalInfoArr, null, 'ResGlobalInfo'));
        $roomStays = '';
        foreach ($roomStaysArr as $roomStay) {
            $roomStays .= str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($roomStay, null, 'RoomStay'));
        }
        $roomStays = '<RoomStays>' . $roomStays . '</RoomStays>';
        $resGuests = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($resGuestsArr, null, 'ResGuests'));
        $iata = '';
        if (isset($filters['travel_agency_identifier'])) {
            $iata = '<UniqueID Type="5" ID="' . $filters['travel_agency_identifier'] . '"/>';
        }

        return '<OTA_HotelResRQ Target="'.$this->credentials->target.'" Version="1.003" TimeStamp="' . $this->timeStamp . '" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                <POS>
                    <Source>
                        <RequestorID Type="18" ID="Partner"/>
                        <BookingChannel Type="2" Primary="true">
                            <CompanyName>HBSI</CompanyName>
                        </BookingChannel>
                    </Source>
                </POS>
                <HotelReservations>
                <HotelReservation RoomStayReservation="true" CreateDateTime="' . date('Y-m-d\TH:i:sP') . '" CreatorID="Partner">
                    ' . $iata . '
                    <UniqueID Type="14" ID="' . $bookingItem->booking_item . '"/>
                    ' . $roomStays . '
                    ' . $resGuests . '
                    ' . $resGlobalInfo . '
                </HotelReservation>
            </HotelReservations>
        </OTA_HotelResRQ>';
    }

    /**
     * @param array $reservation
     * @return string
     */
    private function readRQ(array $reservation): string
    {
        $type = Arr::get($reservation, 'type', 8);

        return '<OTA_ReadRQ Target="'.$this->credentials->target.'" Version="1.003" TimeStamp="' . $this->timeStamp . '" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                    <ReadRequests>
                    <ReadRequest>
                        <UniqueID Type="'.$type.'" ID="' . $reservation['bookingId'] . '"/>
                        <Verification>
                            <PersonName>
                                <GivenName>' . $reservation['main_guest']['GivenName'] . '</GivenName>
                                <Surname>' . $reservation['main_guest']['Surname'] . '</Surname>
                            </PersonName>
                        </Verification>
                    </ReadRequest>
                </ReadRequests>
            </OTA_ReadRQ>';
    }

    /**
     * @param array $reservation
     * @return string
     */
    private function cancelRQ(array $reservation): string
    {
        $type = Arr::get($reservation, 'type', 8);

        return '<OTA_CancelRQ Target="'.$this->credentials->target.'" Version="1.003" TimeStamp="' . $this->timeStamp . '" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                    <POS>
                        <Source>
                            <RequestorID Type="18" ID="HBSI"/>
                            <BookingChannel Type="2" Primary="true">
                                <CompanyName>HBSI</CompanyName>
                            </BookingChannel>
                        </Source>
                    </POS>
                    <UniqueID Type="'.$type.'" ID="' . $reservation['bookingId'] . '">
                        <CompanyName>HBSI</CompanyName>
                    </UniqueID>
                    <Verification>
                        <PersonName>
                            <GivenName>' . $reservation['main_guest']['GivenName'] . '</GivenName>
                            <Surname>' . $reservation['main_guest']['Surname'] . '</Surname>
                        </PersonName>
                    </Verification>
            </OTA_CancelRQ>';
    }

    /**
     * @param string $body
     * @return bool
     */
    private function isXml(string $body): bool
    {
        if (str_contains($body, 'soap-env:Envelope')) return true;
        return false;
    }

    /**
     * @param array $occupancies
     * @return string
     */
    private function occupancyToXml(array $occupancies): string
    {
        $xml = new SimpleXMLElement('<RoomStayCandidates/>');

        foreach ($occupancies as $occupancy) {
            $roomStayCandidate = $xml->addChild('RoomStayCandidate');
            $roomStayCandidate->addAttribute('RoomTypeCode', '*');
            $roomStayCandidate->addAttribute('Quantity', '1');
            $roomStayCandidate->addAttribute('RPH', '1');
            $roomStayCandidate->addAttribute('RatePlanCandidateRPH', '1');

            $guestCounts = $roomStayCandidate->addChild('GuestCounts');

            // Add adults
            $guestCount = $guestCounts->addChild('GuestCount');
            $guestCount->addAttribute('AgeQualifyingCode', '10');
            $guestCount->addAttribute('Count', $occupancy['adults']);

            // Add children and infants
            if (isset($occupancy['children_ages'])) {
                foreach ($occupancy['children_ages'] as $age) {
                    $guestCount = $guestCounts->addChild('GuestCount');
                    $guestCount->addAttribute('AgeQualifyingCode', $age > 2 ? '8' : '7');
                    $guestCount->addAttribute('Count', '1');
                }
            }
        }

        return str_replace('<?xml version="1.0"?>', '', $xml->asXML());
    }

    /**
     * @param array $array
     * @param SimpleXMLElement|null $xml
     * @param string $parentName
     * @return string
     * @throws Exception
     */
    private function arrayToXml(array $array, SimpleXMLElement $xml = null, string $parentName = 'root'): string
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement('<' . $parentName . '/>');
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if ($key === '@attributes') {
                    foreach ($value as $attributeKey => $attributeValue) {
                        $xml->addAttribute($attributeKey, htmlspecialchars($attributeValue));
                    }
                } else {
                    if (is_numeric($key)) {
                        $key = rtrim($parentName, 's');
                    }

                    //If the value is an array of Scalars, we must create nodes with the key name.
                    if (is_scalar(Arr::get($value, '0')))
                    {
                        foreach($value as $value2)
                        {
                            $xml->addChild($key, $value2);
                        }
                    }

                    //If not a sacalar value, it's a probably a nested array.
                    else
                    {
                        $subnode = $xml->addChild($key);
                        $this->arrayToXml($value, $subnode, $key);
                    }
                }
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }

    /**
     * @param array $response
     * @param array $bookingItemData
     * @param array $filters
     * @param array $guests
     * @return array
     */
    private function processRoomStaysArr(array $response, array $bookingItemData, array $filters, array $guests): array
    {
        $res = [];
        $RPH = 1;

        $ratesBookingItemData = explode(';', $bookingItemData['rate_ordinal']);

        foreach ($ratesBookingItemData as $keyRate => $numberRateBookingItemData) {
            $roomByQuery = $keyRate + 1;

            $roomType = explode(';', $bookingItemData['room_id']);
            $ratesBaseResponse = $roomStaysArr = $response['results']['HBSI'][$bookingItemData['hotel_supplier_id']]['rooms'][$roomType[$keyRate]]['rates'];
            foreach ($ratesBaseResponse as $rate) {
                if ($rate['rate_ordinal'] === intval($numberRateBookingItemData)) {
                    $roomStaysArr = $rate;
                    break;
                }
            }

            if (!isset($roomStaysArr['RoomRates']['RoomRate']['Rates']['Rate']['@attributes'])) {
                $newRates = $roomStaysArr['RoomRates']['RoomRate']['Rates']['Rate'];
                unset($roomStaysArr['RoomRates']['RoomRate']['Rates']);
                $roomStaysArr['RoomRates']['RoomRate']['Rates'] = $newRates;
            }
            unset($roomStaysArr['rate_ordinal'], $roomStaysArr['RoomRates']['RoomRate']['RoomRateDescription'], $roomStaysArr['RoomRates']['CancelPenalties']);
            if (isset($filters['special_requests'])) {
                foreach ($filters['special_requests'] as $specialRequest) {
                    if ($specialRequest['booking_item'] === $filters['booking_item'] && $specialRequest['room'] === $roomByQuery) {
                        $roomStaysArr['SpecialRequests'][]['@attributes']['Text'] = $specialRequest['special_request'];
                    }
                }
            }
            if (isset($filters['comments'])) {
                foreach ($filters['comments'] as $commentRequest) {
                    if ($commentRequest['booking_item'] === $filters['booking_item'] && $commentRequest['room'] === $roomByQuery) {
                        $roomStaysArr['Comments'][]['@attributes']['Text'] = $commentRequest['comment'];
                    }
                }
            }
            for ($i = 0; $i < count(array_values($guests)[$keyRate]); $i++) {
                $roomStaysArr['ResGuestRPHs'][]['@attributes']['RPH'] = strval($RPH);
                $RPH++;
            }
            if (isset($roomStaysArr['GuestCounts']['GuestCount']) && count($roomStaysArr['GuestCounts']['GuestCount']) > 1) {
                $guestCounts = $roomStaysArr['GuestCounts']['GuestCount'];
                unset($roomStaysArr['GuestCounts']);
                $roomStaysArr['GuestCounts'] = $guestCounts;
            }

            $res[$keyRate] = $roomStaysArr;
        }

        return $res;
    }

    /**
     * @param array $guests
     * @param array $filters
     * @return array
     */
    private function processResGuestsArr(array $guests, array $filters): array
    {
        $resGuestsArr = [];
        $index = 0;
        foreach ($guests as $guestRoom) {
            foreach ($guestRoom as $guest) {
                $dob = Carbon::parse($guest['date_of_birth']);
                $diff = $dob->diff(Carbon::parse());
                $age = $diff->y;

                $ageQualifyingCode = 10;
                if ($age < 3) {
                    $ageQualifyingCode = 7;
                } elseif ($age < 18) {
                    $ageQualifyingCode = 8;
                }
                $resGuestsArr[$index] = $this->createGuestArr($index, $ageQualifyingCode, $guest, $filters);
                if ($index === 0) {
                    $this->mainGuest = $resGuestsArr[$index]['Profiles']['ProfileInfo']['Profile']['Customer'];
                }
                $index++;
            }
        }
        return $resGuestsArr;
    }

    /**
     * @param int $index
     * @param int $ageQualifyingCode
     * @param array $guest
     * @param array $filters
     * @return array
     */
    private function createGuestArr(int $index, int $ageQualifyingCode, array $guest, array $filters): array
    {
        $guestArr = [];
        $guestArr['@attributes']['ResGuestRPH'] = $index + 1;
        $guestArr['@attributes']['AgeQualifyingCode'] = $ageQualifyingCode;
        if ($index === 0) {
            $guestArr['Profiles']['ProfileInfo']['Profile']['Customer'] = $this->createCustomerArr($guest, $filters);
        } else {
            $guestArr['Profiles']['ProfileInfo']['Profile']['Customer']['PersonName']['GivenName'] = $guest['given_name'];
            $guestArr['Profiles']['ProfileInfo']['Profile']['Customer']['PersonName']['Surname'] = $guest['family_name'];
        }
        return $guestArr;
    }

    /**
     * @param array $guest
     * @param array $filters
     * @return array
     */
    private function createCustomerArr(array $guest, array $filters): array
    {
        $customer = [];
        $customer['PersonName']['GivenName'] = $guest['given_name'];
        $customer['PersonName']['Surname'] = $guest['family_name'];
        $customer['Telephone']['@attributes']['PhoneNumber'] = $filters['booking_contact']['phone']['country_code'] . $filters['booking_contact']['phone']['area_code'] . $filters['booking_contact']['phone']['number'];
        $customer['Email'] = $filters['booking_contact']['email'];
        $customer['Address'] = $this->createAddressArr($filters);
        return $customer;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function createAddressArr(array $filters): array
    {
        $address = [];
        $address['AddressLine'] = $filters['booking_contact']['address']['line_1'];
        $address['CityName'] = $filters['booking_contact']['address']['city'];
        $address['StateProv']['@attributes']['StateCode'] = $filters['booking_contact']['address']['state_province_code'];
        $address['PostalCode'] = $filters['booking_contact']['address']['postal_code'];
        $address['CountryName']['@attributes']['Code'] = $filters['booking_contact']['address']['country_code'];
        return $address;
    }

    /**
     * @param array $filters
     * @param array $roomStaysArr
     * @return array
     */
    private function processDepositPaymentsArr(array $filters, array $roomStaysArr): array
    {
        $depositPaymentsArr = [];
        foreach ($filters['credit_cards'] as $creditCard) {
            if ($creditCard['booking_item'] === $filters['booking_item']) {
                $amount = 0;
                foreach ($roomStaysArr as $roomStay) {
                    $amount += $roomStay['Total']['@attributes']['AmountAfterTax'];
                }
                $depositPaymentsArr = $this->createDepositPaymentArr($creditCard, $amount);
            }
        }
        $resGlobalInfo['DepositPayments'] = $depositPaymentsArr;
        return $resGlobalInfo;
    }

    /**
     * @param array $creditCard
     * @param float $amount
     * @return array
     */
    private function createDepositPaymentArr(array $creditCard, float $amount): array
    {
        $depositPaymentArr = [];
        $depositPaymentArr['RequiredPayment']['AcceptedPayments']['AcceptedPayment']['PaymentCard']['@attributes']['CardType'] = '1';
        $depositPaymentArr['RequiredPayment']['AcceptedPayments']['AcceptedPayment']['PaymentCard']['@attributes']['CardCode'] = $creditCard['credit_card']['card_type'];
        $depositPaymentArr['RequiredPayment']['AcceptedPayments']['AcceptedPayment']['PaymentCard']['@attributes']['CardNumber'] = $creditCard['credit_card']['number'];
        $expiryDate = $creditCard['credit_card']['expiry_date'];
        $expiryDate = Carbon::createFromFormat('m/Y', $expiryDate);
        $month = $expiryDate->format('m');
        $year = $expiryDate->format('y');
        $formattedExpiryDate = $month . $year;
        $depositPaymentArr['RequiredPayment']['AcceptedPayments']['AcceptedPayment']['PaymentCard']['@attributes']['ExpireDate'] = $formattedExpiryDate;
        $depositPaymentArr['RequiredPayment']['AmountPercent']['@attributes']['Amount'] = $amount;
        $depositPaymentArr['RequiredPayment']['Deadline']['@attributes']['AbsoluteDeadline'] = Carbon::createFromFormat('m/Y', $creditCard['credit_card']['expiry_date'])->format('Y-m-d');
        return $depositPaymentArr;
    }
}
