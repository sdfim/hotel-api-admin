<?php

namespace Modules\API\Suppliers\HbsiSupplier;

use App\Repositories\ConfigRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * DELETE THIS FILE AND MOVE TEST CASES TO A DOC WHEN MULTI ROOM IS FULLY INTEGRATED.
 */
class MultiRoomTesting
{
    private const URL = 'https://uat.demandmatrix.net/app/dm/xml/tentravel/search';

    public function __construct(private readonly Client $client = new Client(),
        private readonly array $headers = [
            'Content-Type' => 'text/xml; charset=UTF8',
        ])
    {

        $this->requestId = time().'_tentravel';
        $this->timeStamp = date('Y-m-d\TH:i:sP');
    }

    /**
     * Searches 2 Rooms
     * 2 Adults
     */
    public function execute($scenario, $action)
    {
        switch ($scenario) {
            /**
             * SCENARIO 1:
             * Book Room Only with 4 Adults, and 2 Child in 2 rooms for 5 nights(2 adults and 1 child in each room) -- Verify rates by person if policy is applied - (Same rate plan-Same room type- Same Occupancy)
             */
            case '1':
                $params1 = [
                    'from' => '2024-06-01',
                    'to' => '2024-06-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch1($params1));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook1($params1));
                }

                if ($action === 'cancel') {
                    return $this->sendRequest($this->getCancel1($params1, '1a828875-7f85-4e4a-8921-553e6658d019'));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params1, '721bntDczG'));
                }

                break;

                /**
                 * SCENARIO 2:
                 * Book 2 Adults and 2 children in 1 room and 2 adults and 1 child in another room. Verify correct splitting of guests. (Same rate plan-Same room type- Different Occupancy)
                 */
            case '2':
                $params2 = [
                    'from' => '2024-07-01',
                    'to' => '2024-07-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch2($params2));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook2($params2));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel2($params2, 'caa30ad1-a263-4c46-b96b-90d8768ff7e5'));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params2, ''));
                }

                break;

                /**
                 * SCENARIO 3:
                 * Book 2 Rooms with 2 Adults for 5 nights  (Same rate plan-Different room type- Same Occupancy)
                 */
            case '3':
                $params3 = [
                    'from' => '2024-08-01',
                    'to' => '2024-08-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Double',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch3($params3));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook3($params3));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel3($params3, ''));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params3, ''));
                }
                break;

                /**
                 * SCENARIO 4:
                 * Book 2 rooms with 2 different occupancies. 2 adults and 1 child in one room and 3 adults in second room  (Different rate plan-Different room type- Same Occupancy) (Different occupancy  in same rateplan-roomtype)
                 */
            case '4':
                $params4 = [
                    'from' => '2024-09-01',
                    'to' => '2024-09-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch4($params4));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook4($params4));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel4($params4, ''));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params4, ''));
                }
                break;

                /**
                 * SCENARIO 5:
                 * Book 2 rooms with 2 different rate plan  for 5 nights 2 adults (Different Rate Plan same occupancy  same roomtype)
                 */
            case '5':
                $params5 = [
                    'from' => '2024-05-01',
                    'to' => '2024-05-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Suite',
                        'rate' => 'BAR',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch5($params5));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook5($params5));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel5($params5, '721rF0EQik'));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params5, ''));
                }
                break;

                /**
                 * SCENARIO 6:
                 * Book 2 rooms with 2 different occupancies. 2 adult and 1 child in one room and 3 adults in second room (Different occupancy  in different rateplan-same roomtype)
                 */
            case '6':
                $params6 = [
                    'from' => '2024-07-01',
                    'to' => '2024-07-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Suite',
                        'rate' => 'BAR',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch6($params6));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook6($params6));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel6($params6, ''));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params6, ''));
                }
                break;

                /**
                 * SCENARIO 7:
                 * Book 2 rooms with 2 different occupancies. 1 adult and 1 child in one room and 3 adults in second room (Different occupancy  in different roomtype and different rateplan)
                 */
            case '7':
                $params7 = [
                    'from' => '2024-09-01',
                    'to' => '2024-09-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Double',
                        'rate' => 'BAR',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch7($params7));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook7($params7));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel7($params7, ''));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params7, ''));
                }
                break;

                /**
                 * SCENARIO 8:
                 * Book Room Only with 2 Adults with Special Requests (if Partner Supports)
                 */
            case '8':
                $params8 = [
                    'from' => '2024-09-01',
                    'to' => '2024-09-06',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch8($params8));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook8($params8));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel8($params8, ''));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params8, ''));
                }
                break;

                /**
                 * SCENARIO 2:
                 * Book 2 rooms with same occupancies. 2 adults and 1 child each room  (Different rate plan-Different room type- Same Occupancy)
                 */
            case '9':
                $params9 = [
                    'from' => '2024-08-08',
                    'to' => '2024-08-10',
                    'hotelId' => '51721',
                    'room1' => [
                        'type' => 'Suite',
                        'rate' => 'Promo',
                        'meal' => 'NoM',
                    ],
                    'room2' => [
                        'type' => 'Double',
                        'rate' => 'BAR',
                        'meal' => 'NoM',
                    ],
                    'bookingId' => Str::uuid()->toString(),
                ];

                if ($action === 'search') {
                    $this->sendRequest($this->getSearch9($params9));
                }

                if ($action === 'book') {
                    //Book
                    $this->sendRequest($this->getBook9($params9));
                }

                if ($action === 'cancel') {
                    $this->sendRequest($this->getCancel9($params9, '8bd92e6a-bf2b-498f-971c-df7ab68f0309'));
                }

                if ($action === 'read') {
                    $this->sendRequest($this->readRq($params9, '721KeMSE0b'));
                }

                break;
        }

        return true;
    }

    /** case 1 */
    public function getSearch1($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook1($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-06-01" ExpireDate="2024-06-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-06-01" ExpireDate="2024-06-03">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-06-03" ExpireDate="2024-06-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-06-04" ExpireDate="2024-06-06">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1" />
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-06-01" Duration="P5N" End="2024-06-06" />
                            <Total AmountBeforeTax="1260.00" AmountAfterTax="1260.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="2"/>
                                <ResGuestRPH RPH="5"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-06-01" ExpireDate="2024-06-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-06-01" ExpireDate="2024-06-03">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-06-03" ExpireDate="2024-06-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-06-04" ExpireDate="2024-06-06">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1" />
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-06-01" Duration="P5N" End="2024-06-06" />
                            <Total AmountBeforeTax="1260.00" AmountAfterTax="1260.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="3"/>
                                <ResGuestRPH RPH="4"/>
                                <ResGuestRPH RPH="6"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>




                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult3</GivenName>
                                                <Surname>Adult3 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult4</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="5" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child1</GivenName>
                                                <Surname>Child1 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="6" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child2</GivenName>
                                                <Surname>Child2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel1($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 2 */
    public function getSearch2($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="2"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook2($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>

                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-07-01" ExpireDate="2024-07-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-07-01" ExpireDate="2024-07-03">
                                            <Base AmountBeforeTax="361.67" AmountAfterTax="361.67" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="32.88">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="723.34" AmountAfterTax="723.34" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-07-03" ExpireDate="2024-07-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-07-04" ExpireDate="2024-07-06">
                                            <Base AmountBeforeTax="361.67" AmountAfterTax="361.67" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="32.88">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="723.34" AmountAfterTax="723.34" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                                <GuestCount AgeQualifyingCode="8" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-07-01" Duration="P5N" End="2024-07-06" />
                            <Total AmountBeforeTax="1446.68" AmountAfterTax="1446.68" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="2"/>
                                <ResGuestRPH RPH="5"/>
                                <ResGuestRPH RPH="6"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="2"/>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-07-01" ExpireDate="2024-07-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-07-01" ExpireDate="2024-07-03">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-07-03" ExpireDate="2024-07-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-07-04" ExpireDate="2024-07-06">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1" />
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-07-01" Duration="P5N" End="2024-07-06" />
                            <Total AmountBeforeTax="1260.00" AmountAfterTax="1260.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="3"/>
                                <ResGuestRPH RPH="4"/>
                                <ResGuestRPH RPH="7"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>

                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult3</GivenName>
                                                <Surname>Adult3 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult4</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="5" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child1</GivenName>
                                                <Surname>Child1 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="6" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child2</GivenName>
                                                <Surname>Child2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="7" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child3</GivenName>
                                                <Surname>Child3 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel2($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 3 */
    public function getSearch3($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook3($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-08-01" ExpireDate="2024-08-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-08-01" ExpireDate="2024-08-03">
                                            <Base AmountBeforeTax="300.00" AmountAfterTax="300.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="27.27">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="600.00" AmountAfterTax="600.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-08-03" ExpireDate="2024-08-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-08-04" ExpireDate="2024-08-06">
                                            <Base AmountBeforeTax="300.00" AmountAfterTax="300.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="27.27">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="600.00" AmountAfterTax="600.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-08-01" Duration="P5N" End="2024-08-06" />
                            <Total AmountBeforeTax="1200.00" AmountAfterTax="1200.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="2"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-08-01" ExpireDate="2024-08-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-08-01" ExpireDate="2024-08-03">
                                            <Base AmountBeforeTax="250.00" AmountAfterTax="250.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="22.73">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="500.00" AmountAfterTax="500.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-08-03" ExpireDate="2024-08-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-08-04" ExpireDate="2024-08-06">
                                            <Base AmountBeforeTax="250.00" AmountAfterTax="250.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="22.73">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="500.00" AmountAfterTax="500.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-08-01" Duration="P5N" End="2024-08-06" />
                            <CancelPenalties>
                                <CancelPenalty PolicyCode="CKP">
                                    <AmountPercent Percent="20" Amount="200.00" />
                                </CancelPenalty>
                            </CancelPenalties>
                            <Total AmountBeforeTax="1000.00" AmountAfterTax="1000.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="3"/>
                                <ResGuestRPH RPH="4"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>

                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult3</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult4</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel3($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 4 */
    public function getSearch4($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="3"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook4($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-09-01" ExpireDate="2024-09-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-01" ExpireDate="2024-09-03">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-09-03" ExpireDate="2024-09-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-04" ExpireDate="2024-09-06">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1" />
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-09-01" Duration="P5N" End="2024-09-06" />
                            <Total AmountBeforeTax="1260.00" AmountAfterTax="1260.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="2"/>
                                <ResGuestRPH RPH="6"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-09-01" ExpireDate="2024-09-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-01" ExpireDate="2024-09-03">
                                            <Base AmountBeforeTax="350.00" AmountAfterTax="350.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="31.82">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="700.00" AmountAfterTax="700.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-09-03" ExpireDate="2024-09-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-04" ExpireDate="2024-09-06">
                                            <Base AmountBeforeTax="350.00" AmountAfterTax="350.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="31.82">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="700.00" AmountAfterTax="700.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="3" />
                            </GuestCounts>
                            <TimeSpan Start="2024-09-01" Duration="P5N" End="2024-09-06" />
                            <Total AmountBeforeTax="1400.00" AmountAfterTax="1400.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="3"/>
                                <ResGuestRPH RPH="4"/>
                                <ResGuestRPH RPH="5"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="3"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>




                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult3</GivenName>
                                                <Surname>Adult3 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult4</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="5" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult5</GivenName>
                                                <Surname>Adult5 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="6" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child1</GivenName>
                                                <Surname>Child1 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel4($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 5 */
    public function getSearch5($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook5($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>

                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-05-01" ExpireDate="2024-05-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-05-01" ExpireDate="2024-05-03">
                                            <Base AmountBeforeTax="300.00" AmountAfterTax="300.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="27.27">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="600.00" AmountAfterTax="600.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-05-03" ExpireDate="2024-05-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-05-04" ExpireDate="2024-05-06">
                                            <Base AmountBeforeTax="300.00" AmountAfterTax="300.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="27.27">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="600.00" AmountAfterTax="600.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-05-01" Duration="P5N" End="2024-05-06" />
                            <Total AmountBeforeTax="1200.00" AmountAfterTax="1200.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="2"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-05-01" ExpireDate="2024-05-06" RatePlanCode="BAR">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="5" EffectiveDate="2024-05-01" ExpireDate="2024-05-06">
                                            <Base AmountBeforeTax="300.00" AmountAfterTax="300.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="27.27">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="1500.00" AmountAfterTax="1500.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-05-01" Duration="P5N" End="2024-05-06" />
                            <CancelPenalties>
                                <CancelPenalty PolicyCode="CXP" NonRefundable="true">
                                    <Deadline AbsoluteDeadline="2024-03-21T00:00:00" />
                                    <AmountPercent Percent="100" />
                                </CancelPenalty>
                            </CancelPenalties>
                            <Total AmountBeforeTax="1500.00" AmountAfterTax="1500.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="3"/>
                                <ResGuestRPH RPH="4"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>

                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult3</GivenName>
                                                <Surname>Adult3 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult4</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel5($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 6 */
    public function getSearch6($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="3"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook6($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-07-01" ExpireDate="2024-07-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-07-01" ExpireDate="2024-07-03">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-07-03" ExpireDate="2024-07-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-07-04" ExpireDate="2024-07-06">
                                            <Base AmountBeforeTax="315.00" AmountAfterTax="315.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="28.64">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="630.00" AmountAfterTax="630.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1" />
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-07-01" Duration="P5N" End="2024-07-06" />
                            <Total AmountBeforeTax="1260.00" AmountAfterTax="1260.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="2"/>
                                <ResGuestRPH RPH="6"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-07-01" ExpireDate="2024-07-06" RatePlanCode="BAR">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="5" EffectiveDate="2024-07-01" ExpireDate="2024-07-06">
                                            <Base AmountBeforeTax="350.00" AmountAfterTax="350.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="31.82">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="1750.00" AmountAfterTax="1750.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="3" />
                            </GuestCounts>
                            <TimeSpan Start="2024-07-01" Duration="P5N" End="2024-07-06" />
                            <CancelPenalties>
                                <CancelPenalty PolicyCode="CXP" NonRefundable="true">
                                    <Deadline AbsoluteDeadline="2024-03-19T00:00:00" />
                                    <AmountPercent Percent="100" />
                                </CancelPenalty>
                            </CancelPenalties>
                            <Total AmountBeforeTax="1750.00" AmountAfterTax="1750.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="3"/>
                                <ResGuestRPH RPH="4"/>
                                <ResGuestRPH RPH="5"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="3"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>




                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult3</GivenName>
                                                <Surname>Adult3 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult4</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="5" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult5</GivenName>
                                                <Surname>Adult5 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="6" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child1</GivenName>
                                                <Surname>Child1 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel6($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 7 */
    public function getSearch7($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="1"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="3"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook7($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>

                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-09-01" ExpireDate="2024-09-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-01" ExpireDate="2024-09-03">
                                            <Base AmountBeforeTax="275.00" AmountAfterTax="275.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="25.00">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="550.00" AmountAfterTax="550.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-09-03" ExpireDate="2024-09-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-04" ExpireDate="2024-09-06">
                                            <Base AmountBeforeTax="275.00" AmountAfterTax="275.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="25.00">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="550.00" AmountAfterTax="550.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1" />
                                <GuestCount AgeQualifyingCode="10" Count="1" />
                            </GuestCounts>
                            <TimeSpan Start="2024-09-01" Duration="P5N" End="2024-09-06" />
                            <Total AmountBeforeTax="1100.00" AmountAfterTax="1100.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="5"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="1"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-09-01" ExpireDate="2024-09-06" RatePlanCode="BAR">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="5" EffectiveDate="2024-09-01" ExpireDate="2024-09-06">
                                            <Base AmountBeforeTax="350.00" AmountAfterTax="350.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="31.82">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="1750.00" AmountAfterTax="1750.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="3" />
                            </GuestCounts>
                            <TimeSpan Start="2024-09-01" Duration="P5N" End="2024-09-06" />
                            <CancelPenalties>
                                <CancelPenalty PolicyCode="CXP" NonRefundable="true">
                                    <Deadline AbsoluteDeadline="2024-03-19T00:00:00" />
                                    <AmountPercent Percent="100" />
                                </CancelPenalty>
                            </CancelPenalties>
                            <Total AmountBeforeTax="1750.00" AmountAfterTax="1750.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="2"/>
                                <ResGuestRPH RPH="3"/>
                                <ResGuestRPH RPH="4"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="3"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>




                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult3</GivenName>
                                                <Surname>Adult3 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult4</GivenName>
                                                <Surname>Adult4 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="5" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child1</GivenName>
                                                <Surname>Child1 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel7($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 7 */
    public function getSearch8($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="2"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook8($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>

                            <RoomRates>
                                <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-09-01" ExpireDate="2024-09-06" RatePlanCode="Promo">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-01" ExpireDate="2024-09-03">
                                            <Base AmountBeforeTax="300.00" AmountAfterTax="300.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="27.27">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="600.00" AmountAfterTax="600.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="1" EffectiveDate="2024-09-03" ExpireDate="2024-09-04">
                                            <Base AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                            <Total AmountBeforeTax="0.00" AmountAfterTax="0.00" CurrencyCode="USD" />
                                        </Rate>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-09-04" ExpireDate="2024-09-06">
                                            <Base AmountBeforeTax="300.00" AmountAfterTax="300.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="27.27">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="600.00" AmountAfterTax="600.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2" />
                            </GuestCounts>
                            <TimeSpan Start="2024-09-01" Duration="P5N" End="2024-09-06" />
                            <Total AmountBeforeTax="1200.00" AmountAfterTax="1200.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="2"/>
                            </ResGuestRPHs>
                            <SpecialRequests>
                                <SpecialRequest>
                                    <Text >Request crib in room</Text>
                                </SpecialRequest>
                            </SpecialRequests>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="10" Count="2"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>

                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel8($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    /** case 9 */
    public function getSearch9($params)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <soap-env:Envelope
                    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap-env:Header>
                        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                        </Interface>
                    </soap-env:Header>
                    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelAvailRQ">
                        <OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
                            xmlns="http://www.opentravel.org/OTA/2003/05" BestOnly="false" SummaryOnly="false" >
                            <POS>Ï
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
                                            <StayDateRange Start="'.$params['from'].'" Duration="Day" End="'.$params['to'].'"></StayDateRange>
                                            <RateRange RateTimeUnit="Day" CurrencyCode="USD" ></RateRange>
                                            <RatePlanCandidates>
                                                <RatePlanCandidate RatePlanCode="*" RPH="1">
                                                    <HotelRefs>
                                                        <HotelRef HotelCode="51721" />
                                                        <HotelRef HotelCode="51722" />
                                                    </HotelRefs>
                                                    <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                                </RatePlanCandidate>
                                            </RatePlanCandidates>
                                            <RoomStayCandidates>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="1"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                                <RoomStayCandidate RoomTypeCode="*" Quantity="1" RPH="1" RatePlanCandidateRPH="1">
                                                    <GuestCounts>
                                                        <GuestCount AgeQualifyingCode="10" Count="1"/>
                                                        <GuestCount AgeQualifyingCode="8" Count="1"/>
                                                    </GuestCounts>
                                                </RoomStayCandidate>
                                            </RoomStayCandidates>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </soap-env:Body>
                </soap-env:Envelope>
        ';
    }

    public function getBook9($params)
    {
        $bookingId = $params['bookingId'];

        $room1Type = $params['room1']['type'];
        $room1Rate = $params['room1']['rate'];
        $room1Meal = $params['room1']['meal'];

        $room2Type = $params['room2']['type'];
        $room2Rate = $params['room2']['rate'];
        $room2Meal = $params['room2']['meal'];

        return '<?xml version="1.0" encoding="utf-8"?>
<soap-env:Envelope
    xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
    <soap-env:Header>
        <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
            xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
            <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
        </Interface>
    </soap-env:Header>
    <soap-env:Body RequestId="'.$this->requestId.'" Transaction="HotelResRQ">
        <OTA_HotelResRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
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
                <HotelReservation RoomStayReservation="true" CreateDateTime="'.$this->timeStamp.'" CreatorID="Partner">
                    <UniqueID Type="14" ID="'.$bookingId.'"/>
                    <RoomStays>
                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room1Type.'">
                                    <RoomDescription Name="'.$room1Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room1Rate.'">
                                    <RatePlanDescription Name="'.$room1Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room1Meal.'"/>
                                </RatePlan>
                            </RatePlans>

                            <RoomRates>
                            <RoomRate RoomTypeCode="Suite" NumberOfUnits="1" EffectiveDate="2024-08-08" ExpireDate="2024-08-10" RatePlanCode="Promo">
                                <Rates>
                                    <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-08-08" ExpireDate="2024-08-10">
                                        <Base AmountBeforeTax="275.00" AmountAfterTax="275.00" CurrencyCode="USD">
                                            <Taxes>
                                                <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="25.00">
                                                    <TaxDescription>
                                                        <Text>Occupancy Tax</Text>
                                                    </TaxDescription>
                                                </Tax>
                                            </Taxes>
                                        </Base>
                                        <Total AmountBeforeTax="550.00" AmountAfterTax="550.00" CurrencyCode="USD" />
                                    </Rate>
                                </Rates>
                            </RoomRate>
                        </RoomRates>
                        <GuestCounts>
                            <GuestCount AgeQualifyingCode="8" Count="1" />
                            <GuestCount AgeQualifyingCode="10" Count="1" />
                        </GuestCounts>
                        <TimeSpan Start="2024-08-08" Duration="P2N" End="2024-08-10" />
                        <Total AmountBeforeTax="550.00" AmountAfterTax="550.00" CurrencyCode="USD" />
                        <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                            <Address>
                                <AddressLine>Address</AddressLine>
                                <CityName>Miami</CityName>
                                <StateProv StateCode="FL" />
                                <CountryName>USA</CountryName>
                            </Address>
                        </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="1"/>
                                <ResGuestRPH RPH="3"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="1"/>
                            </GuestCounts>
                        </RoomStay>

                        <RoomStay>
                            <RoomTypes>
                                <RoomType NumberOfUnits="1" RoomTypeCode="'.$room2Type.'">
                                    <RoomDescription Name="'.$room2Type.'">
                                        <Text/>
                                    </RoomDescription>
                                </RoomType>
                            </RoomTypes>
                            <RatePlans>
                                <RatePlan RatePlanCode="'.$room2Rate.'">
                                    <RatePlanDescription Name="'.$room2Rate.'">
                                        <Text/>
                                    </RatePlanDescription>
                                    <MealsIncluded MealPlanCodes="'.$room2Meal.'"/>
                                </RatePlan>
                            </RatePlans>


                            <RoomRates>
                                <RoomRate RoomTypeCode="Double" NumberOfUnits="1" EffectiveDate="2024-08-08" ExpireDate="2024-08-10" RatePlanCode="BAR">
                                    <Rates>
                                        <Rate RateTimeUnit="Day" UnitMultiplier="2" EffectiveDate="2024-08-08" ExpireDate="2024-08-10">
                                            <Base AmountBeforeTax="250.00" AmountAfterTax="250.00" CurrencyCode="USD">
                                                <Taxes>
                                                    <Tax Type="Inclusive" Code="Occupancy Tax" Percent="10" Amount="22.73">
                                                        <TaxDescription>
                                                            <Text>Occupancy Tax</Text>
                                                        </TaxDescription>
                                                    </Tax>
                                                </Taxes>
                                            </Base>
                                            <Total AmountBeforeTax="500.00" AmountAfterTax="500.00" CurrencyCode="USD" />
                                        </Rate>
                                    </Rates>
                                </RoomRate>
                            </RoomRates>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1" />
                                <GuestCount AgeQualifyingCode="10" Count="1" />
                            </GuestCounts>
                            <TimeSpan Start="2024-08-08" Duration="P2N" End="2024-08-10" />
                            <CancelPenalties>
                                <CancelPenalty PolicyCode="CFC">
                                    <Deadline AbsoluteDeadline="2024-03-24T14:07:49" />
                                    <AmountPercent Amount="0.00" />
                                </CancelPenalty>
                            </CancelPenalties>
                            <Total AmountBeforeTax="500.00" AmountAfterTax="500.00" CurrencyCode="USD" />
                            <BasicPropertyInfo HotelCode="51721" HotelName="TestProperty1_Ram">
                                <Address>
                                    <AddressLine>Address</AddressLine>
                                    <CityName>Miami</CityName>
                                    <StateProv StateCode="FL" />
                                    <CountryName>USA</CountryName>
                                </Address>
                            </BasicPropertyInfo>
                            <ResGuestRPHs>
                                <ResGuestRPH RPH="2"/>
                                <ResGuestRPH RPH="4"/>
                            </ResGuestRPHs>
                            <GuestCounts>
                                <GuestCount AgeQualifyingCode="8" Count="1"/>
                                <GuestCount AgeQualifyingCode="10" Count="1"/>
                            </GuestCounts>
                        </RoomStay>
                    </RoomStays>

                    <ResGuests>
                        <ResGuest ResGuestRPH="1" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult1</GivenName>
                                                <Surname>Adult1 Surname</Surname>
                                            </PersonName>
                                            <Telephone PhoneNumber="584243097654"/>
                                            <Email>ramonlv93@gmail.com</Email>
                                            <Address>
                                                <AddressLine>Miami Gardens</AddressLine>
                                                <CityName>Valle De La Pascua</CityName>
                                                <StateProv StateCode="FL"/>
                                                <PostalCode>33166</PostalCode>
                                                <CountryName Code="US"/>
                                            </Address>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="2" AgeQualifyingCode="10">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Adult2</GivenName>
                                                <Surname>Adult2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="3" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child1</GivenName>
                                                <Surname>Child1 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>

                        <ResGuest ResGuestRPH="4" AgeQualifyingCode="8">
                            <Profiles>
                                <ProfileInfo>
                                    <Profile>
                                        <Customer>
                                            <PersonName>
                                                <GivenName>Child2</GivenName>
                                                <Surname>Child2 Surname</Surname>
                                            </PersonName>
                                        </Customer>
                                    </Profile>
                                </ProfileInfo>
                            </Profiles>
                        </ResGuest>
                    </ResGuests>
                    <ResGlobalInfo>
                        <DepositPayments/>
                    </ResGlobalInfo>
                </HotelReservation>

            </HotelReservations>
        </OTA_HotelResRQ>
    </soap-env:Body>
</soap-env:Envelope>
        ';
    }

    public function getCancel9($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="CancelRQ">
                    <OTA_CancelRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source>
                                <RequestorID Type="18" ID="HBSI"/>
                                <BookingChannel Type="2" Primary="true">
                                    <CompanyName>HBSI</CompanyName>
                                </BookingChannel>
                            </Source>
                        </POS>
                        <UniqueID Type="15" ID="'.$id.'">
                            <CompanyName>HBSI</CompanyName>
                        </UniqueID>
                        <Verification>
                            <PersonName>
                                <GivenName>Adult1</GivenName>
                                <Surname>Adult1 Surname</Surname>
                            </PersonName>
                        </Verification>
                    </OTA_CancelRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    public function readRq($params, $id)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="TenTraval_XML4T" Version="2006A" Interface="HBSI XML 4 OTA"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="'.$params['hotelId'].'" User="tentravel" Pwd="xml4t!" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="ReadRQ">
                    <OTA_ReadRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'" ResStatus="Commit"
                xmlns="http://www.opentravel.org/OTA/2003/05">
                    <ReadRequests>
                        <ReadRequest>
                            <UniqueID Type="14" ID="'.$id.'"/>
                            <Verification>
                                <PersonName>
                                    <GivenName>Adult1</GivenName>
                                    <Surname>Adult1 Surname</Surname>
                                </PersonName>
                            </Verification>
                        </ReadRequest>
                    </ReadRequests>
                </OTA_ReadRQ>
                </soap-env:Body>
            </soap-env:Envelope>
        ';
    }

    private function sendRequest($body)
    {
        Log::info('--------------------------------------------------------  REQUEST --------------------------------------------------------');
        Log::info($body);

        $response = $this->client->request('POST', self::URL, [
            'headers' => $this->headers,
            'body' => $body,
            'timeout' => ConfigRepository::getTimeout(),
        ]);

        $contents = $response->getBody()->getContents();
        Log::info('--------------------------------------------------------  RESPONSE --------------------------------------------------------');
        Log::info($contents);

        return $contents;
    }
}
