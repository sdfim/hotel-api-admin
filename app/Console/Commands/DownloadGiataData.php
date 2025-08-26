<?php

namespace App\Console\Commands;

use App\Models\Mapping;
use App\Models\Property;
use App\Models\Supplier;
use App\Traits\ExceptionReportTrait;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;
use Modules\API\Suppliers\Enums\PropertiesSourceEnum;
use Modules\Inspector\ExceptionReportController;

class DownloadGiataData extends Command
{
    use ExceptionReportTrait;

    protected $signature = 'download-giata-data';

    protected $description = 'Import XML data from a URL, wrtite to DB';

    protected float|string $current_time;

    protected array $execution_times = [];

    protected int $giata_id = 0;

    protected ?string $report_id;

    public function __construct(
        protected ExceptionReportController $apiExceptionReport
    ) {
        parent::__construct();
        $this->execution_times['main'] = microtime(true);
        $this->execution_times['step'] = microtime(true);
        $this->execution_times['report'] = microtime(true);
    }

    public function handle(): void
    {
        // Property::truncate();
        $this->giata_id = Supplier::where('name', 'Giata')->first()?->id ?? 0;
        $this->report_id = Str::uuid()->toString();

        $this->saveSuccessReport('DownloadGiataData', 'Start downloading data', json_encode([
            'execution_time' => $this->executionTime('report').' sec',
        ]));

        $this->current_time = microtime(true);

        // Статистика для итоговых отчетов
        $statsProperties = 0;
        $statsHBSI = 0;
        $statsExpedia = 0;
        $statsIcePortal = 0;

        $batch = 1;
        $url = config('giata.main.base_uri').'properties';
        $username = config('giata.main.username');
        $password = config('giata.main.password');

        $this->saveSuccessReport('DownloadGiataData', 'Start downloading data', json_encode([
            'url' => $url,
            'execution_time' => $this->executionTime('report').' sec',
        ]));

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

                    list($url, $batchStats) = $this->parseXMLToDb($textXML, $batch);

                    // Агрегируем статистику из каждой пачки
                    $statsProperties += $batchStats['properties'] ?? 0;
                    $statsHBSI += $batchStats['hbsi'] ?? 0;
                    $statsExpedia += $batchStats['expedia'] ?? 0;
                    $statsIcePortal += $batchStats['iceportal'] ?? 0;

                    $this->info('parseXMLToDb BATCH: '.$batch.' in '.$this->executionTime().' seconds');

                    $batch++;

                    $this->info('XML data imported successfully, BATCH: '.$batch);
                    $this->info('Memory usage: '.(memory_get_usage() / 1024 / 1024).' MB');
                    $this->warn('-----------------------------------');

                } else {
                    $error = 'Error importing XML data. HTTP status code: '.$response->getStatusCode();
                    $this->error($error);
                    $this->saveErrorReport('DownloadGiataData', 'HTTP Error', json_encode([
                        'batch' => $batch,
                        'status_code' => $response->getStatusCode(),
                        'execution_time' => $this->executionTime('report').' sec',
                    ]));
                }
            } catch (Exception|GuzzleException $e) {
                $error = 'Error importing XML data: '.$e->getMessage();
                $this->error($error);
                $this->saveErrorReport('DownloadGiataData', 'Exception', json_encode([
                    'batch' => $batch,
                    'getMessage' => $e->getMessage(),
                    'getTraceAsString' => $e->getTraceAsString(),
                    'execution_time' => $this->executionTime('report').' sec',
                ]));
            }
        }

        //Restore to normal
        DB::connection('mysql_cache')->setEventDispatcher($eventDispatcher);
        DB::enableQueryLog();

        // Добавляем финальный отчет со статистикой по поставщикам
        $this->saveSuccessReport('DownloadGiataData', 'All data processed successfully', json_encode([
            'total_batches' => $batch - 1,
            'total_execution_time' => (microtime(true) - $this->execution_times['main']).' sec',
            'memory_peak_usage' => (memory_get_peak_usage() / 1024 / 1024).' MB',
            'total_properties' => $statsProperties,
            'suppliers_mapping_stats' => [
                'hbsi' => $statsHBSI,
                'expedia' => $statsExpedia,
                'iceportal' => $statsIcePortal,
            ]
        ]));
    }

    private function executionTime(string $key = null): float|string
    {
        if ($key) {
            $execution_time = (microtime(true) - $this->execution_times[$key]);
            $this->execution_times[$key] = microtime(true);
            return $execution_time;
        }

        $execution_time = (microtime(true) - $this->current_time);
        $this->current_time = microtime(true);

        return $execution_time;
    }

    private function parseXMLToDb(string $text, int $batch): array
    {
        $xmlContent = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $text);

        $xml = simplexml_load_string($xmlContent);
        $properties = $xml->TTI_Property;

        $batchDataMapperHbsi = [];
        $batchDataMapperExpedia = [];
        $batchDataMapperIcePortal = [];
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

        foreach ($properties as $property) {
            if (isset($propertiesToNotUpdate[$property['code']]) && $propertiesToNotUpdate[$property['code']]) {
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

            $latitude = isset($property->Position['Latitude']) ? (float) $property->Position['Latitude'] : null;
            $longitude = isset($property->Position['Longitude']) ? (float) $property->Position['Longitude'] : null;

            $data = [
                'code' => (int) $property['Code'],
                'search_index' => (string) $property->Name.' '.(int) $property['Code'],
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
                'latitude' => $latitude,
                'longitude' => $longitude,
                'url' => $url,
                'cross_references' => $crossReferences,
                'rating' => $property->Ratings ? (float) $property->Ratings[0]->Rating['Value'] : 0.0,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            foreach ($property->CrossReferences->CrossReference as $crossReference) {
                if ((string) $crossReference['Code'] == 'FORA_IBS' && (string) $crossReference['Status'] !== 'Inactive') {
                    $batchDataMapperHbsi[] = [
                        'supplier_id' => $crossReference->Code['HotelCode'],
                        'giata_id' => (int) $property['Code'],
                        'supplier' => MappingSuppliersEnum::HBSI->value,
                        'match_percentage' => 100,
                    ];
                }

                if ((string) $crossReference['Code'] == 'EXPEDIA_RAPID' && (string) $crossReference['Status'] !== 'Inactive') {
                    $batchDataMapperExpedia[] = [
                        'supplier_id' => $crossReference->Code['HotelCode'],
                        'giata_id' => (int) $property['Code'],
                        'supplier' => MappingSuppliersEnum::Expedia->value,
                        'match_percentage' => 100,
                    ];
                }

                if ((string) $crossReference['Code'] == 'ICEPORTAL' && (string) $crossReference['Status'] !== 'Inactive') {
                    $batchDataMapperIcePortal[] = [
                        'supplier_id' => $crossReference->Code['HotelCode'],
                        'giata_id' => (int) $property['Code'],
                        'supplier' => MappingSuppliersEnum::IcePortal->value,
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

            $this->saveErrorReport('DownloadGiataData', 'Error inserting properties', json_encode([
                'batch' => $batch,
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));

            return [false, ['properties' => 0, 'hbsi' => 0, 'expedia' => 0, 'iceportal' => 0]];
        }

        try {
            DB::beginTransaction();
            Mapping::HBSI()->whereIn('giata_id', $propertyIds)->delete();
            Mapping::insert($batchDataMapperHbsi);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImportJsonlData insert Mapping ', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());

            $this->saveErrorReport('DownloadGiataData', 'Error inserting HBSI mappings', json_encode([
                'batch' => $batch,
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));

            return [false, ['properties' => count($batchData), 'hbsi' => 0, 'expedia' => 0, 'iceportal' => 0]];
        }

        try {
            DB::beginTransaction();
            Mapping::Expedia()->whereIn('giata_id', $propertyIds)->delete();
            Mapping::insert($batchDataMapperExpedia);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImportJsonlData insert Mapping for Expedia', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());

            $this->saveErrorReport('DownloadGiataData', 'Error inserting Expedia mappings', json_encode([
                'batch' => $batch,
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));

            return [false, ['properties' => count($batchData), 'hbsi' => count($batchDataMapperHbsi), 'expedia' => 0, 'iceportal' => 0]];
        }

        try {
            DB::beginTransaction();
            Mapping::IcePortal()->whereIn('giata_id', $propertyIds)->delete();
            Mapping::insert($batchDataMapperIcePortal);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImportJsonlData insert Mapping for Ice Portal', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());

            $this->saveErrorReport('DownloadGiataData', 'Error inserting IcePortal mappings', json_encode([
                'batch' => $batch,
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));

            return [false, ['properties' => count($batchData), 'hbsi' => count($batchDataMapperHbsi), 'expedia' => count($batchDataMapperExpedia), 'iceportal' => 0]];
        }

        $url = false;
        try {
            $url_next = explode('<More_Properties xlink:href=', $xmlContent)[1];
            $url_arr = explode('"', $url_next);
            $url = array_key_exists(1, $url_arr) ? $url_arr[1] : false;
            $this->comment('Get next url: '.$url);
        } catch (Exception $e) {
            $this->comment('Url not found - all data retrieved. This is the last batch');
        }

        // Собираем статистические данные для этого батча
        $stats = [
            'properties' => count($batchData),
            'hbsi' => count($batchDataMapperHbsi),
            'expedia' => count($batchDataMapperExpedia),
            'iceportal' => count($batchDataMapperIcePortal)
        ];

        unset($batchData, $batchDataMapperHbsi, $batchDataMapperExpedia, $propertyIds, $properties, $xml, $xmlContent);

        // Возвращаем массив с URL и статистикой
        return [$url, $stats];
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
