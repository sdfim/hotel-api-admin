<?php

namespace Modules\API\Suppliers\HbsiSupplier;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use SimpleXMLElement;


class HbsiClient
{

    private const USERNAME = 'tentravel';
    private const PASSWORD = 'xml4t!';
    private const COMPONENT_INFO_ID = '51721';
    private const URL = 'https://uat.demandmatrix.net/app/dm/xml/tentravel/search';
    private const CHANNEL_IDENTIFIER_ID = 'TenTraval_XML4T';
    private const VERSION = '2006A';
    private const INTERFACE = 'HBSI XML 4 OTA';
    private string $requestId;
    private string $timeStamp;

    /**
     * @param Client $client
     * @param array $headers
     */
    public function __construct(
        private readonly Client $client = new Client(),
        private readonly array $headers = [
            'Content-Type' => 'text/xml; charset=UTF8',
        ],
    )
    {
        $this->requestId = time() . '_tentravel';
        $this->timeStamp = date('Y-m-d\TH:i:sP');
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function getHbsiPriceByPropertyIds(array $hotelIds, array $filters) : ?array
    {
        $body = $this->makeRequest($this->hotelAvailRQ($hotelIds,$filters), 'HotelAvailRQ');
        $response = $this->client->request('POST', self::URL, ['headers' => $this->headers, 'body' => $body]);
        $body = $response->getBody();

        if ($this->isXml($body)) {
            try {
                return  [
                    'request' => $body,
                    'response' => new SimpleXMLElement($body, LIBXML_NOCDATA)
                ];
            } catch (\Exception $e) {
                \Log::error('HbsiClient getHbsiPriceByPropertyIds ' . $e->getMessage());
                return null;
            }
        } else {
             return null;
        }
    }

    /**
     * @param string $body
     * @param string $typeRequest
     * @return string
     */
    private function makeRequest(string $body, string $typeRequest) : string
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">
                <soap-env:Header>
                    <Interface ChannelIdentifierId="' . self::CHANNEL_IDENTIFIER_ID . '" Version="' . self::VERSION . '" Interface="' . self::INTERFACE . '"
                        xmlns="http://www.hbsiapi.com/Documentation/XML/OTA/4/2005A/">
                        <ComponentInfo Id="' . self::COMPONENT_INFO_ID . '" User="' . self::USERNAME . '" Pwd="' . self::PASSWORD . '" ComponentType="Hotel"/>
                    </Interface>
                </soap-env:Header>
                <soap-env:Body RequestId="'.$this->requestId.'" Transaction="'.$typeRequest.'">
                    ' . $body . '
                </soap-env:Body>
            </soap-env:Envelope>';
    }

    /**
     * @param array $hotelIds
     * @param array $params
     * @return string
     */
    private function hotelAvailRQ(array $hotelIds, array $params = []) : string
    {
        if (empty($hotelIds)) $hotelIds = ['51722', '51721'];
        foreach ($hotelIds as $hotelId) {
            $hotelRefs[] = '<HotelRef HotelCode="'.$hotelId.'" />';
        }
        $cityCode = $params['cityCode'] ?? 'MIA';   // MIA - Miami, IATA City Code
        $start = $params['checkin'] ?? '2024-02-10';
        $end = $params['checkout'] ?? '2024-02-15';
        $currency = $params['currency'] ?? 'USD';
        $roomStayCandidates = $this->occupancyToXml($params['occupancy']);

        return '<OTA_HotelAvailRQ Target="Test" Version="1.003" TimeStamp="'.$this->timeStamp.'"
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
                                <StayDateRange Start="'.$start.'" Duration="Day" End="'.$end.'"></StayDateRange>
                                <RateRange RateTimeUnit="Day" CurrencyCode="'.$currency.'" ></RateRange>
                                <RatePlanCandidates>
                                    <RatePlanCandidate RatePlanCode="*" RPH="1">
                                        <HotelRefs>
                                            ' . implode('', $hotelRefs) . '
                                        </HotelRefs>
                                        <MealsIncluded MealPlanCodes="*"></MealsIncluded>
                                    </RatePlanCandidate>
                                </RatePlanCandidates>
                                '.$roomStayCandidates.'
                            </Criterion>
                        </HotelSearchCriteria>
                    </AvailRequestSegment>
                </AvailRequestSegments>
            </OTA_HotelAvailRQ>';
    }

    /**
     * @param $body
     * @return bool
     */
    private function isXml($body) : bool
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
}
