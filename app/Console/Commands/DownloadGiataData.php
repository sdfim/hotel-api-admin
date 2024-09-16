<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\Mapping;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;
use Modules\API\Suppliers\Enums\PropertiesSourceEnum;

class DownloadGiataData extends Command
{
    protected $signature = 'download-giata-data';

    protected $description = 'Import XML data from a URL, wrtite to DB';

    protected float|string $current_time;

    public function handle(): void
    {
        // Property::truncate();

        $this->current_time = microtime(true);

        $batch = 1;
        $url = config('giata.main.base_uri').'properties';
        $username = config('giata.main.username');
        $password = config('giata.main.password');

        // Create a Guzzle HTTP client instance
        $client = new Client([
            'auth' => [$username, $password],
        ]);

        //This code prevents memory overflow
        DB::disableQueryLog();
        $eventDispatcher = DB::connection('mysql_cache')->getEventDispatcher();
        DB::connection('mysql_cache')->unsetEventDispatcher();

        while ($url) {
            try {
                // Send an HTTP GET request with authentication
                $response = $client->get($url);

                $this->info(' GET request  BATCH: '.$batch.' in '.$this->executionTime().' seconds');

                if ($response->getStatusCode() === 200) {
                    // Get the XML content from the response body
                    $textXML = $response->getBody()->getContents();

                    $this->info('Get XML BATCH: '.$batch.' in '.$this->executionTime().' seconds');

                    $url = $this->parseXMLToDb($textXML);

                    $this->info('parseXMLToDb BATCH: '.$batch.' in '.$this->executionTime().' seconds');

                    $batch++;

                    $this->info('XML data imported successfully, BATCH: '.$batch);
                    $this->info('Memory usage: '.(memory_get_usage() / 1024 / 1024).' MB');
                    $this->warn('-----------------------------------');

                } else {
                    $this->error('Error importing XML data. HTTP status code: '.$response->getStatusCode());
                }
            } catch (Exception|GuzzleException $e) {
                $this->error('Error importing XML data: '.$e->getMessage());
            }
        }

        //Restore to normal
        DB::connection('mysql_cache')->setEventDispatcher($eventDispatcher);
        DB::enableQueryLog();

    }

    private function executionTime(): float|string
    {
        $execution_time = (microtime(true) - $this->current_time);
        $this->current_time = microtime(true);

        return $execution_time;
    }

    private function parseXMLToDb(string $text): bool|string
    {
        $xmlContent = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $text);

        $xml = simplexml_load_string($xmlContent);
        $proterties = $xml->TTI_Property;

        $batchDataMapperHbsi = [];
        $batchData = [];
        $propertyIds = [];
        $propertiesToNotUpdate = Property::where('property_auto_updates', 0)
          ->orWhereNot('source', PropertiesSourceEnum::Giata->value)
          ->get()
          ->mapWithKeys(function ($value) {
            return [
                $value->code => true,
            ];
          })
          ->toArray();

        foreach ($proterties as $property) {
            if (!blank($propertiesToNotUpdate[$property['code']])) {
              continue;
            }

            $phones = [];
            if (isset($property->Phone)) {
                foreach ($property->Phone as $phone) {
                    $phoneArray = json_decode(json_encode($phone), true);
                    $phones[] = $phoneArray['@attributes'];
                }
            }

            $chain = $this->processProperty($property, 'Chain');
            $crossReferences = $this->processProperty($property, 'CrossReferences');
            $address = $this->processProperty($property, 'Address');
            $position = $this->processProperty($property, 'Position');
            $url = $this->processProperty($property, 'URL');

            $data = [
                'code' => (int) $property['Code'],
                'last_updated' => (string) $property['LastUpdated'],
                'name' => (string) $property->Name,
                'chain' => $chain,
                'city' => (string) $property->City,
                'city_id' => (int) $property->City['CityId'],
                'locale' => (string) $property->Locale,
                'locale_id' => (int) $property->Locale['LocaleId'],
                'address' => $address,
                'mapper_address' => (string) $property->Address->StreetNmbr.' '.(string) $property->Address->AddressLine,
                'mapper_postal_code' => (string) $property->Address->PostalCode,
                'mapper_phone_number' => (string) $property->Phone['PhoneNumber'],
                'phone' => $phones ? json_encode($phones) : null,
                'position' => $position,
                'latitude' => isset($property->Position['Latitude']) ? (float) $property->Position['Latitude'] : null,
                'longitude' => isset($property->Position['Longitude']) ? (float) $property->Position['Longitude'] : null,
                'url' => $url,
                'cross_references' => $crossReferences,
                'rating' => $property->Ratings ? (float) $property->Ratings[0]->Rating['Value'] : 0.0,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            foreach ($property->CrossReferences->CrossReference as $crossReference) {
                if ((string) $crossReference['Code'] == 'ULTIMATE_JET_VACATIONS' && (string) $crossReference['Status'] !== 'Inactive') {
                    $batchDataMapperHbsi[] = [
                        'supplier_id' => $crossReference->Code['HotelCode'],
                        'giata_id' => (int) $property['Code'],
                        'supplier' => MappingSuppliersEnum::HBSI->value,
                        'match_percentage' => 100,
                    ];
                }
            }

            $batchData[] = $data;
            $propertyIds[] = $data['code'];
        }

        try {
            DB::beginTransaction();
            Property::whereIn('code', $propertyIds)->delete();

            // can overflow memory. if there is a memory overflow, the following block must be used
            Property::insert($batchData);
            // this block will not overflow memory, but it is slower because it inserts records one by one.
            // foreach ($batchData as $data) {
            //     Property::create($data);
            // }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImportJsonlData insert Property ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());

            return false;
        }

        try {
            DB::beginTransaction();
            Mapping::hBSI()->whereIn('giata_id', $propertyIds)->delete();
            Mapping::insert($batchDataMapperHbsi);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImportJsonlData insert Mapping ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());

            return false;
        }

        try {
            $url_next = explode('<More_Properties xlink:href=', $xmlContent)[1];
            $url_arr = explode('"', $url_next);
            $url = array_key_exists(1, $url_arr) ? $url_arr[1] : false;
            $this->comment('Get next url: '.$url);
        } catch (Exception $e) {
            $this->comment('Url not found - all data retrieved. This is the last batch');

            return false;
        }

        unset($batchData, $batchDataMapperHbsi, $propertyIds, $proterties, $xml, $xmlContent);

        return $url;
    }

    private function processProperty($property, $key)
    {
        $result = null;
        if (isset($property->$key)) {
            $array = json_decode(json_encode($property->$key), true);
            $result = json_encode($this->removeAttributesKey($array));
        }

        return $result;
    }

    private function removeAttributesKey($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($key === '@attributes') {
                if (is_array($value)) {
                    $result = array_merge($result, $this->removeAttributesKey($value));
                } else {
                    $result[] = $value;
                }
            } else {
                if (is_array($value)) {
                    $result[$key] = $this->removeAttributesKey($value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
