<?php

namespace App\Console\Commands;

use App\Models\GiataProperty;
use App\Models\MapperHbsiGiata;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DownloadGiataData extends Command
{

    protected $signature = 'download-giata-data';

    protected $description = 'Import XML data from a URL, wrtite to DB';

    protected float|string $current_time;

    public function handle(): void
    {
        // GiataProperty::truncate();

        $this->current_time = microtime(true);

        $batch = 1;
        $url = config('giata.main.base_uri') . 'properties';
        $username = config('giata.main.username');
        $password = config('giata.main.password');

        // Create a Guzzle HTTP client instance
        $client = new Client([
            'auth' => [$username, $password],
        ]);

        //This code prevents memory overflow
        DB::disableQueryLog();
        $eventDispatcher = DB::connection('mysql2')->getEventDispatcher();
        DB::connection('mysql2')->unsetEventDispatcher();


        while ($url) {
            try {
                // Send an HTTP GET request with authentication
                $response = $client->get($url);

                $this->info(' GET request  BATCH: ' . $batch . ' in ' . $this->executionTime() . ' seconds');

                if ($response->getStatusCode() === 200) {
                    // Get the XML content from the response body
                    $textXML = $response->getBody()->getContents();

                    $this->info('Get XML BATCH: ' . $batch . ' in ' . $this->executionTime() . ' seconds');

                    $url = $this->parseXMLToDb($textXML);
                    if (!$url) $url = $this->parseXMLToDb($textXML);

                    $this->info('parseXMLToDb BATCH: ' . $batch . ' in ' . $this->executionTime() . ' seconds');

                    $batch++;

                    $this->info('XML data imported successfully, BATCH: ' . $batch);
                    $this->info('Memory usage: ' . (memory_get_usage() / 1024 / 1024) . ' MB');
                    $this->warn('-----------------------------------');

                } else {
                    $this->error('Error importing XML data. HTTP status code: ' . $response->getStatusCode());
                }
            } catch (Exception|GuzzleException $e) {
                $this->error('Error importing XML data: ' . $e->getMessage());
            }
        }

        //Restore to normal
        DB::connection('mysql2')->setEventDispatcher($eventDispatcher);
        DB::enableQueryLog();;

    }

    /**
     * @return float|string
     */
    private function executionTime(): float|string
    {
        $execution_time = (microtime(true) - $this->current_time);
        $this->current_time = microtime(true);

        return $execution_time;
    }

    /**
     * @param string $text
     * @return false|string
     */
    private function parseXMLToDb(string $text): false|string
    {
        $xmlContent = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $text);
        try {
            $url_next = explode('<More_Properties xlink:href=', $xmlContent)[1];
            $url_arr = explode('"', $url_next);
            $url = array_key_exists(1, $url_arr) ? $url_arr[1] : false;
            $this->comment('Get next url: ' . $url);
        } catch (Exception $e) {
            $this->error('Error get url or it not exist: ' . $e->getMessage());
            return false;
        }

        $xml = simplexml_load_string($xmlContent);
        $proterties = $xml->TTI_Property;

        $batchDataMapperHbsi = [];
        $batchData = [];
        $propertyIds = [];
        foreach ($proterties as $property) {

            $data = [
                'code' => (int)$property['Code'],
                'last_updated' => (string)$property['LastUpdated'],
                'name' => (string)$property->Name,
                'chain' => isset($property->Chain) ? json_encode($property->Chain) : null,
                'city' => (string)$property->City,
                'city_id' => (int)$property->City['CityId'],
                'locale' => (string)$property->Locale,
                'locale_id' => (int)$property->Locale['LocaleId'],
                'address' => json_encode($property->Address),
                'mapper_address' => (string)$property->Address->StreetNmbr . ' ' . (string)$property->Address->AddressLine,
                'mapper_postal_code' => (string)$property->Address->PostalCode,
                'mapper_phone_number' => (string)$property->Phone['PhoneNumber'],
                'phone' => isset($property->Phone) ? json_encode($property->Phone) : null,
                'position' => isset($property->Position) ? json_encode($property->Position) : null,
                'latitude' => isset($property->Position['Latitude']) ? (float)$property->Position['Latitude'] : null,
                'longitude' => isset($property->Position['Longitude']) ? (float)$property->Position['Longitude'] : null,
                'url' => isset($property->URL) ? json_encode($property->URL) : null,
                'cross_references' => json_encode($property->CrossReferences),
                'rating' => $property->Ratings ? (float)$property->Ratings[0]->Rating['Value'] : 0.0,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            foreach ($property->CrossReferences->CrossReference as $crossReference) {
                if( (string)$crossReference['Code'] == 'ULTIMATE_JET_VACATIONS' && (string)$crossReference['Status'] !== 'Inactive') {
                    $batchDataMapperHbsi[] = [
                        'hbsi_id' => $crossReference->Code['HotelCode'],
                        'giata_id' => (int)$property['Code'],
                        'perc' => 100,
                    ];
                }
            }

            $batchData[] = $data;
            $propertyIds[] = $data['code'];
        }

        try {
            DB::beginTransaction();
            GiataProperty::whereIn('code', $propertyIds)->delete();

            // can overflow memory. if there is a memory overflow, the following block must be used
            GiataProperty::insert($batchData);
            // this block will not overflow memory, but it is slower because it inserts records one by one.
            // foreach ($batchData as $data) {
            //     GiataProperty::create($data);
            // }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImportJsonlData insert GiataProperty ', ['error' => $e->getMessage()]);
            return false;
        }

        try {
            DB::beginTransaction();
            MapperHbsiGiata::whereIn('giata_id', $propertyIds)->delete();
            MapperHbsiGiata::insert($batchDataMapperHbsi);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImportJsonlData insert MapperHbsiGiata ', ['error' => $e->getMessage()]);
            return false;
        }

        unset($batchData, $batchDataMapperHbsi, $propertyIds, $proterties, $xml, $xmlContent);

        return $url;
    }
}
