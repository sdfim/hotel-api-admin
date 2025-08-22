<?php

namespace App\Console\Commands\Hbsi;

use App\Models\HbsiProperty;
use Illuminate\Console\Command;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;

class FetchHbsiContent extends Command
{
    protected $signature = 'hbsi:fetch-content {hotelCode} {hotelName?}';

    protected $description = 'Fetch hotel descriptive content from HBSI API';

    public function handle()
    {
        $hotelCode = $this->argument('hotelCode');
        $hotelName = $this->argument('hotelName') ?? '';

        $client = app(HbsiClient::class);
        $result = $client->fetchContent($hotelCode, $hotelName);

        if ($result && isset($result['response'])) {
            $xml = $result['response'];
            $content = $xml->xpath('//soap-env:Body/*')[0] ?? $xml;
            $hotel = $content->HotelDescriptiveContents->HotelDescriptiveContent ?? null;
            if ($hotel) {
                $addressObj = $hotel->ContactInfos->ContactInfo->Addresses->Address ?? null;
                // Парсинг адреса в отдельные переменные
                $addressLine = (string) ($addressObj->AddressLine ?? '');
                $cityName = (string) ($addressObj->CityName ?? '');
                $postalCode = (string) ($addressObj->PostalCode ?? '');
                $state = (string) ($addressObj->StateProv['StateCode'] ?? '');
                $countryName = (string) ($addressObj->CountryName ?? '');

                $phoneObj = $hotel->ContactInfos->ContactInfo->Phones->Phone ?? null;
                $phone = $phoneObj ? (string) $phoneObj['PhoneNumber'] : null;
                $emailsObj = $hotel->ContactInfos->ContactInfo->Emails->Email ?? [];
                $emails = [];
                foreach ($emailsObj as $email) {
                    $emails[] = (string) $email;
                }
                $rateplans = [];
                $roomtypes = [];
                $tpa_extensions = [];

                $tpaExtensionsNode = $hotel->TPA_Extensions->TPA_Extension ?? null;
                if ($tpaExtensionsNode) {
                    foreach ($tpaExtensionsNode->Extension as $ext) {
                        $name = (string) $ext['Name'];
                        $items = [];
                        foreach ($ext->Item ?? [] as $item) {
                            $itemArr = [
                                'key' => (string) $item['Key'],
                                'value' => (string) $item['Value'],
                                'text' => trim((string) $item),
                            ];
                            if ($item->Detail) {
                                $details = [];
                                foreach ($item->Detail as $detail) {
                                    $details[] = [
                                        'key' => (string) $detail['Key'],
                                        'value' => (string) $detail['Value'],
                                    ];
                                }
                                $itemArr['details'] = $details;
                            }
                            $items[] = $itemArr;
                        }
                        if ($name === 'Rateplans') {
                            $rateplans = $items;
                        } elseif ($name === 'Roomtypes') {
                            $roomtypes = $items;
                        } else {
                            $tpa_extensions[$name] = $items;
                        }
                    }
                }

                HbsiProperty::updateOrCreate(
                    ['hotel_code' => (string) $hotel['HotelCode']],
                    [
                        'hotel_name' => (string) $hotel['HotelName'],
                        'city_code' => (string) $hotel['HotelCityCode'],
                        'address_line' => $addressLine,
                        'city_name' => $cityName,
                        'state' => $state,
                        'postal_code' => $postalCode,
                        'country_name' => $countryName,
                        'phone' => $phone,
                        'emails' => $emails,
                        'rateplans' => $rateplans,
                        'roomtypes' => $roomtypes,
                        'tpa_extensions' => $tpa_extensions,
                        'raw_xml' => $xml->asXML(),
                    ]
                );
                $this->info('Content parsed and saved to HbsiProperty.');
            } else {
                $this->error('No hotel descriptive content found in response.');
            }
        } else {
            $this->error('Failed to fetch content.');
        }
    }
}
