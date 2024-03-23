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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use Throwable;

class HbsiClient
{
    private const COMPONENT_INFO_ID = '51721';

    private const URL = 'https://uat.demandmatrix.net/app/dm/xml/tentravel/search';

    private const VERSION = '2006A';

    private const INTERFACE = 'HBSI XML 4 OTA';

    private string $requestId;

    private string $timeStamp;

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
        $client = new \GuzzleHttp\Client();
        $promise = $client->requestAsync('GET', 'https://httpbin.org/get');

//        $bodyQuery = $this->makeRequest($this->hotelAvailRQ($hotelIds, $filters), 'HotelAvailRQ');
//        $promise = $this->client->requestAsync('POST', self::URL, [
//            'headers' => $this->headers,
//            'body' => $bodyQuery,
//            'timeout' => ConfigRepository::getTimeout()
//        ]);

//        $result = Fiber::suspend($promise);

//        $body = $result['value']->getBody()->getContents();

        $result = Fiber::suspend($promise);
        $body = $this->exampleSearchResponse();
        $bodyQuery = '';

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
        $bodyQuery = $this->makeRequest($this->hotelResModifyRQ($filters), 'HotelResModifyRQ');
        $response = $this->sendRequest($bodyQuery);
        $body = $response->getBody();

        return $this->processXmlBody($body, $bodyQuery, true);
    }

    /**
     * @param array $reservation
     * @return array|null
     * @throws GuzzleException
     */
    public function retrieveBooking(array $reservation): ?array
    {
        $bodyQuery = $this->makeRequest($this->readRQ($reservation), 'ReadRQ');
        $response = $this->sendRequest($bodyQuery);
        $body = $response->getBody();
        return $this->processXmlBody($body, $bodyQuery);
    }

    /**
     * @param array $reservation
     * @return array|null
     * @throws GuzzleException
     */
    public function cancelBooking(array $reservation): ?array
    {
        $bodyQuery = $this->makeRequest($this->cancelRQ($reservation), 'CancelRQ');
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
        return $this->client->request('POST', self::URL, [
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
     * @param string $hotelId
     * @return string
     */
    private function makeRequest(string $body, string $typeRequest, string $hotelId = self::COMPONENT_INFO_ID): string
    {
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
        $roomStayCandidates = $this->occupancyToXml($params['occupancy']);

        return '<OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="' . $this->timeStamp . '"
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
                                <StayDateRange Start="' . $params['checkin'] . '" Duration="Day" End="' . $params['checkout'] . '"></StayDateRange>
                                <RateRange RateTimeUnit="Day" CurrencyCode="' . $params['currency'] . '" ></RateRange>
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
        $roomByQuery = $bookingItem->room_by_query;
        $passengersData = ApiBookingInspectorRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $roomStaysArr = $this->processRoomStaysArr($response, $bookingItemData, $filters, $roomByQuery, $guests);
        $resGuestsArr = $this->processResGuestsArr($guests, $roomByQuery, $filters);
        $resGlobalInfoArr = $this->processDepositPaymentsArr($filters, $roomStaysArr);

        $resGlobalInfo = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($resGlobalInfoArr, null, 'ResGlobalInfo'));
        $roomStays = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($roomStaysArr, null, 'RoomStays'));
        $resGuests = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($resGuestsArr, null, 'ResGuests'));
        $iata = '';
        if (isset($filters['travel_agency_identifier'])) {
            $iata = '<UniqueID Type="5" ID="' . $filters['travel_agency_identifier'] . '"/>';
        }

        return '<OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="' . $this->timeStamp . '" ResStatus="Commit"
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
                    <UniqueID Type="14" ID="' . $bookingItem->booking_item . '_' . time() . '"/>
                    ' . $roomStays . '
                    ' . $resGuests . '
                    ' . $resGlobalInfo . '
                </HotelReservation>
            </HotelReservations>
        </OTA_HotelResRQ>';
    }

    /**
     * @param array $filters
     * @return string
     * @throws Exception
     */
    private function hotelResModifyRQ(array $filters): string
    {
        $response = ApiSearchInspectorRepository::getResponse($filters['search_id']);
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingItemData = json_decode($bookingItem->booking_item_data, true);
        $roomByQuery = $bookingItem->room_by_query;
        $passengersData = ApiBookingInspectorRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        $guests = json_decode($passengersData->request, true)['rooms'];

        $roomStaysArr = $this->processRoomStaysArr($response, $bookingItemData, $filters, $roomByQuery, $guests);
        $resGuestsArr = $this->processResGuestsArr($guests, $roomByQuery, $filters);
        $resGlobalInfoArr = $this->processDepositPaymentsArr($filters, $roomStaysArr);

        $resGlobalInfo = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($resGlobalInfoArr, null, 'ResGlobalInfo'));
        $roomStays = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($roomStaysArr, null, 'RoomStays'));
        $resGuests = str_replace('<?xml version="1.0"?>', '', $this->arrayToXml($resGuestsArr, null, 'ResGuests'));


        return '<OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="' . $this->timeStamp . '" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="2024-05-03T15:47:24-04:00" CreatorID="Partner">
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

        return '<OTA_ReadRQ Target="Test" Version="1.003" TimeStamp="' . $this->timeStamp . '" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                    <ReadRequests>
                    <ReadRequest>
                        <UniqueID Type="14" ID="' . $reservation['bookingId'] . '"/>
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

        return '<OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="' . $this->timeStamp . '" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                    <POS>
                        <Source>
                            <RequestorID Type="18" ID="HBSI"/>
                            <BookingChannel Type="2" Primary="true">
                                <CompanyName>HBSI</CompanyName>
                            </BookingChannel>
                        </Source>
                    </POS>
                    <UniqueID Type="15" ID="' . $reservation['ReservationId'] . '">
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
                    $subnode = $xml->addChild($key);
                    $this->arrayToXml($value, $subnode, $key);
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
     * @param int $roomByQuery
     * @param array $guests
     * @return array
     */
    private function processRoomStaysArr(array $response, array $bookingItemData, array $filters, int $roomByQuery, array $guests): array
    {
        $rates = $roomStaysArr = $response['results']['HBSI'][$bookingItemData['hotel_supplier_id']]['rooms'][$bookingItemData['room_id']]['rates'];
        if (isset($rates['rate_ordinal'])) $roomStaysArr = $rates[$bookingItemData['rate_ordinal'] - 1];
        else {
            foreach ($rates as $rate) {
                if ($rate['rate_ordinal'] === $bookingItemData['rate_ordinal']) {
                    $roomStaysArr = $rate;
                    break;
                }
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
        for ($i = 0; $i < count(array_values($guests)[0]); $i++) {
            $roomStaysArr['ResGuestRPHs'][]['@attributes']['RPH'] = strval($i + 1);
        }
        if (isset($roomStaysArr['GuestCounts']['GuestCount']) && count($roomStaysArr['GuestCounts']['GuestCount']) > 1) {
            $guestCounts = $roomStaysArr['GuestCounts']['GuestCount'];
            unset($roomStaysArr['GuestCounts']);
            $roomStaysArr['GuestCounts'] = $guestCounts;
        }

        $roomStaysArrFinal['RoomStay'] = $roomStaysArr;

        return $roomStaysArrFinal;
    }

    /**
     * @param array $guests
     * @param int $roomByQuery
     * @param array $filters
     * @return array
     */
    private function processResGuestsArr(array $guests, int $roomByQuery, array $filters): array
    {
        $resGuestsArr = [];
        foreach ($guests[$roomByQuery] as $index => $guest) {
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
                $depositPaymentsArr = $this->createDepositPaymentArr($creditCard, $roomStaysArr);
            }
        }
        $resGlobalInfo['DepositPayments'] = $depositPaymentsArr;
        return $resGlobalInfo;
    }

    /**
     * @param array $creditCard
     * @param array $roomStaysArr
     * @return array
     */
    private function createDepositPaymentArr(array $creditCard, array $roomStaysArr): array
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
        $depositPaymentArr['RequiredPayment']['AmountPercent']['@attributes']['Amount'] = $roomStaysArr['RoomStay']['Total']['@attributes']['AmountAfterTax'];
        $depositPaymentArr['RequiredPayment']['Deadline']['@attributes']['AbsoluteDeadline'] = Carbon::createFromFormat('m/Y', $creditCard['credit_card']['expiry_date'])->format('Y-m-d');
        return $depositPaymentArr;
    }


    private function exampleSearchResponse(): string
    {
        return '<?xml version="1.0"?>
<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
	<soap-env:Header />
	<soap-env:Body RequestId="1711131423_tentravel" Transaction="HotelAvailRS">
		<OTA_HotelAvailRS xmlns="http://www.opentravel.org/OTA/2003/05"
			TimeStamp="2024-03-22T18:17:05+00:00" Target="Test">
			<Success />

			<RoomStays>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Suite">
							<RoomDescription Name="Suite">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Promo">
							<RatePlanDescription Name="Promo">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Promo">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="202.56" AmountAfterTax="202.56"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="18.41">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="405.12" AmountAfterTax="405.12"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<Total AmountBeforeTax="405.12" AmountAfterTax="405.12" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Double">
							<RoomDescription Name="Double">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Promo">
							<RatePlanDescription Name="Promo">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Promo">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="162.05" AmountAfterTax="162.05"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="14.73">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="324.10" AmountAfterTax="324.10"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CKP">
							<AmountPercent Percent="20" Amount="64.82" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="324.10" AmountAfterTax="324.10" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Suite">
							<RoomDescription Name="Suite">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="BAR">
							<RatePlanDescription Name="BAR">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="BF - Bed and Breakfast" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="BAR">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="202.56" AmountAfterTax="202.56"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="18.41">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="607.68" AmountAfterTax="607.68"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP" NonRefundable="true">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="100" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="607.68" AmountAfterTax="607.68" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Single">
							<RoomDescription Name="Single">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="BAR">
							<RatePlanDescription Name="BAR">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="BF - Bed and Breakfast" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Single" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="BAR">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="121.54" AmountAfterTax="133.69"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Exclusive" Code="Occupancy Tax" Percent="10"
												Amount="12.15">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="364.62" AmountAfterTax="401.07"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP" NonRefundable="true">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="100" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="364.62" AmountAfterTax="401.07" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Double">
							<RoomDescription Name="Double">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="BAR">
							<RatePlanDescription Name="BAR">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="BF - Bed and Breakfast" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="BAR">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="162.05" AmountAfterTax="162.05"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="14.73">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="486.15" AmountAfterTax="486.15"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CFC">
							<Deadline AbsoluteDeadline="2024-03-26T18:17:05" />
							<AmountPercent Amount="0.00" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="486.15" AmountAfterTax="486.15" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Suite">
							<RoomDescription Name="Suite">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Best">
							<RatePlanDescription Name="Best">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Best">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="283.59" AmountAfterTax="283.59"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="25.78">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="850.77" AmountAfterTax="850.77"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="25" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="20" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="850.77" AmountAfterTax="850.77" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Single">
							<RoomDescription Name="Single">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Best">
							<RatePlanDescription Name="Best">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Single" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Best">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="162.05" AmountAfterTax="162.05"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="14.73">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="486.15" AmountAfterTax="486.15"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="25" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="20" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="486.15" AmountAfterTax="486.15" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Double">
							<RoomDescription Name="Double">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Best">
							<RatePlanDescription Name="Best">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Best">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="202.56" AmountAfterTax="202.56"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="18.41">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="607.68" AmountAfterTax="607.68"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="25" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="20" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="607.68" AmountAfterTax="607.68" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Suite">
							<RoomDescription Name="Suite">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Promo">
							<RatePlanDescription Name="Promo">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Promo">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="243.08" AmountAfterTax="243.08"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="22.10">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="486.16" AmountAfterTax="486.16"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<Total AmountBeforeTax="486.16" AmountAfterTax="486.16" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Double">
							<RoomDescription Name="Double">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Promo">
							<RatePlanDescription Name="Promo">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Promo">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="202.56" AmountAfterTax="202.56"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="18.41">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="405.12" AmountAfterTax="405.12"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CKP">
							<AmountPercent Percent="20" Amount="81.03" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="405.12" AmountAfterTax="405.12" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Suite">
							<RoomDescription Name="Suite">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="BAR">
							<RatePlanDescription Name="BAR">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="BF - Bed and Breakfast" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="BAR">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="243.08" AmountAfterTax="243.08"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="22.10">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="729.24" AmountAfterTax="729.24"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP" NonRefundable="true">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="100" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="729.24" AmountAfterTax="729.24" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Double">
							<RoomDescription Name="Double">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="BAR">
							<RatePlanDescription Name="BAR">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="BF - Bed and Breakfast" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="BAR">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="202.56" AmountAfterTax="202.56"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="18.41">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="607.68" AmountAfterTax="607.68"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<Total AmountBeforeTax="607.68" AmountAfterTax="607.68" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Suite">
							<RoomDescription Name="Suite">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Best">
							<RatePlanDescription Name="Best">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Best">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="324.10" AmountAfterTax="324.10"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="29.46">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="972.30" AmountAfterTax="972.30"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="20" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="972.30" AmountAfterTax="972.30" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Double">
							<RoomDescription Name="Double">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Best">
							<RatePlanDescription Name="Best">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Best">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="243.08" AmountAfterTax="243.08"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="22.10">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="729.24" AmountAfterTax="729.24"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="20" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="729.24" AmountAfterTax="729.24" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
						<Address>
							<AddressLine>Address</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="STD">
							<RoomDescription Name="STD">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Loyalty">
							<RatePlanDescription Name="Loyalty">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="STD" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Loyalty">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="121.54" AmountAfterTax="121.54"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="11.05">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="364.62" AmountAfterTax="364.62"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="25" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="364.62" AmountAfterTax="364.62" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Luxury">
							<RoomDescription Name="Luxury">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Loyalty">
							<RatePlanDescription Name="Loyalty">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Luxury" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Loyalty">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="202.56" AmountAfterTax="202.56"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="18.41">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="607.68" AmountAfterTax="607.68"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="25" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="607.68" AmountAfterTax="607.68" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="STD">
							<RoomDescription Name="STD">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="DISC">
							<RatePlanDescription Name="DISC">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="STD" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="DISC">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="121.54" AmountAfterTax="121.54"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="11.05">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="243.08" AmountAfterTax="243.08"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP" NonRefundable="true">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="100" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="243.08" AmountAfterTax="243.08" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Luxury">
							<RoomDescription Name="Luxury">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="DISC">
							<RatePlanDescription Name="DISC">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Luxury" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="DISC">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="243.08" AmountAfterTax="243.08"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="22.10">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="486.16" AmountAfterTax="486.16"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="1" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP" NonRefundable="true">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="100" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="486.16" AmountAfterTax="486.16" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="STD">
							<RoomDescription Name="STD">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Loyalty">
							<RatePlanDescription Name="Loyalty">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="STD" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Loyalty">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="162.06" AmountAfterTax="162.06"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="14.73">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="486.18" AmountAfterTax="486.18"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="25" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="486.18" AmountAfterTax="486.18" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Luxury">
							<RoomDescription Name="Luxury">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="Loyalty">
							<RatePlanDescription Name="Loyalty">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Luxury" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="Loyalty">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="3"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="283.60" AmountAfterTax="283.60"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="25.78">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="850.80" AmountAfterTax="850.80"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="25" />
						</CancelPenalty>
						<CancelPenalty PolicyCode="CXP">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="50" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="850.80" AmountAfterTax="850.80" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="STD">
							<RoomDescription Name="STD">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="DISC">
							<RatePlanDescription Name="DISC">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="STD" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="DISC">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="162.06" AmountAfterTax="162.06"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="14.73">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="324.12" AmountAfterTax="324.12"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP" NonRefundable="true">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="100" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="324.12" AmountAfterTax="324.12" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>


				<RoomStay>
					<RoomTypes>
						<RoomType NumberOfUnits="1" RoomTypeCode="Luxury">
							<RoomDescription Name="Luxury">
								<Text />
							</RoomDescription>
						</RoomType>
					</RoomTypes>
					<RatePlans>
						<RatePlan RatePlanCode="DISC">
							<RatePlanDescription Name="DISC">
								<Text />
							</RatePlanDescription>
							<MealsIncluded MealPlanCodes="NoM" />
						</RatePlan>
					</RatePlans>
					<RoomRates>
						<RoomRate RoomTypeCode="Luxury" NumberOfUnits="1" EffectiveDate="2024-03-23"
							ExpireDate="2024-03-26" RatePlanCode="DISC">
							<Rates>
								<Rate RateTimeUnit="Day" UnitMultiplier="2"
									EffectiveDate="2024-03-23" ExpireDate="2024-03-25">
									<Base AmountBeforeTax="324.10" AmountAfterTax="324.10"
										CurrencyCode="GBP">
										<Taxes>
											<Tax Type="Inclusive" Code="Occupancy Tax" Percent="10"
												Amount="29.46">
												<TaxDescription>
													<Text>Occupancy Tax</Text>
												</TaxDescription>
											</Tax>
										</Taxes>
									</Base>
									<Total AmountBeforeTax="648.20" AmountAfterTax="648.20"
										CurrencyCode="GBP" />
								</Rate>
								<Rate RateTimeUnit="Day" UnitMultiplier="1"
									EffectiveDate="2024-03-25" ExpireDate="2024-03-26">
									<Base AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
									<Total AmountBeforeTax="0.00" AmountAfterTax="0.00"
										CurrencyCode="GBP" />
								</Rate>
							</Rates>

						</RoomRate>
					</RoomRates>
					<GuestCounts>
						<GuestCount AgeQualifyingCode="10" Count="2" />
					</GuestCounts>
					<TimeSpan Start="2024-03-23" Duration="P3N" End="2024-03-26" />
					<DepositPayments>
						<GuaranteePayment>
							<AmountPercent Percent="20" />
							<Deadline AbsoluteDeadline="2024-03-22" />
						</GuaranteePayment>
					</DepositPayments>
					<CancelPenalties>
						<CancelPenalty PolicyCode="CXP" NonRefundable="true">
							<Deadline AbsoluteDeadline="2024-03-22T00:00:00" />
							<AmountPercent Percent="100" />
						</CancelPenalty>
					</CancelPenalties>
					<Total AmountBeforeTax="648.20" AmountAfterTax="648.20" CurrencyCode="GBP" />
					<BasicPropertyInfo HotelCode="51722" HotelName="TestProperty2_Ram">
						<Address>
							<AddressLine>Address1</AddressLine>
							<CityName>Miami</CityName>
							<StateProv StateCode="FL" />
							<CountryName>USA</CountryName>
						</Address>
					</BasicPropertyInfo>
				</RoomStay>
			</RoomStays>
		</OTA_HotelAvailRS>
	</soap-env:Body>
</soap-env:Envelope>';
    }
}
