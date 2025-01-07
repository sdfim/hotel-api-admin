<?php

namespace App\Console\Commands;

use App\Models\ExpediaContent;
use App\Models\ExpediaContentSlave;
use App\Models\GeneralConfiguration;
use App\Models\Property;
use App\Models\Supplier;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Modules\API\Suppliers\Enums\PropertiesSourceEnum;
use Modules\API\Suppliers\ExpediaSupplier\PropertyPriceCall;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Inspector\ExceptionReportController;

class DownloadExpediaData extends Command
{
    use BaseTrait;

    protected $signature = 'download-expedia-data {type} {step} {city?}';

    protected $description = 'Command description';

    protected RapidClient $rapidClient;

    protected ExceptionReportController $apiExceptionReport;

    private const PROPERTY_CONTENT_PATH = 'v3/files/properties/';

    private const BATCH_SIZE = 100;

    private const MIN_RATING = 3;

    private ?string $type;

    private ?string $step;

    private ?string $city;

    protected int $expedia_id = 1;

    protected ?string $report_id;

    protected string $savePath;

    private string $partnerPointSale;

    private string $billingTerms;

    private string $paymentTerms;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(RapidClient $rapidClient)
    {
        parent::__construct();
        $this->rapidClient = $rapidClient;
        $this->apiExceptionReport = new ExceptionReportController();
        $this->current_time['main'] = microtime(true);
        $this->current_time['step'] = microtime(true);
        $this->current_time['report'] = microtime(true);
        $this->savePath = storage_path().'/app';

        $rateType = env('SUPPLIER_EXPEDIA_RATE_TYPE', 'standalone');
        $rates = $rateType === 'package' ? PropertyPriceCall::PACKAGE_RATES : PropertyPriceCall::STANDALONE_RATES;
        $this->partnerPointSale = $rates['partner_point_of_sale'];
        $this->billingTerms = $rates['billing_terms'];
        $this->paymentTerms = $rates['payment_terms'];
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->expedia_id = Supplier::where('name', 'Expedia')->first()->id;

        $this->type = $this->argument('type'); // content
        $this->step = $this->argument('step'); // 1, 2, 3, 4
        $this->city = strtolower($this->argument('city')); // cancun

        $this->report_id = Str::uuid()->toString();

        $this->executionTime('report');

        $result = Process::run('df -h')->output();
        $this->info('DownloadExpediaData df -h: '.$result);
        $result = Process::run('free -h')->output();
        $this->info('DownloadExpediaData free -h: '.$result);

        if (str_contains($this->step, 1)) {
            // get url from expedia
            $url = $this->getUrlArchive();
            $this->info('url from expedia '.$url.' in '.$this->executionTime('step').' seconds');

            $this->saveSuccessReport('DownloadExpediaData', 'Step:1 get url', json_encode([
                'url' => $url,
                'execution_time' => $this->executionTime('report').' sec',
            ]));
        }

        if (str_contains($this->step, 2)) {
            // download file from url and save to storage
            $this->downloadArchive($url);
        }

        if (str_contains($this->step, 3)) {
            // unzip file
            $this->unzipFile();
        }

        if (str_contains($this->step, 4)) {
            // parse json to db
            $this->parseJsonToDb();
        }

        if (str_contains($this->step, 5)) {
            // set inactive status
            $this->setInactiveStatus();
        }
    }

    /**
     * Get url from expedia
     */
    public function getUrlArchive(): string
    {
        $url = '';
        $this->executionTime('report');
        $queryParams = [
            'language' => 'en-US',
            'supply_source' => 'expedia',
            'partner_point_of_sale' => $this->partnerPointSale,
            'billing_terms' => $this->billingTerms,
            'payment_terms' => $this->paymentTerms,
        ];
        try {
            $response = $this->rapidClient->get(self::PROPERTY_CONTENT_PATH.$this->type, $queryParams);
            $propertyContents = $response->getBody()->getContents();
            $url = json_decode($propertyContents, true)['href'];
        } catch (Exception $e) {
            $this->saveErrorReport('DownloadExpediaData', 'getUrlArchive', json_encode([
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));
        }

        return $url;
    }

    /**
     * Download file from url and save to storage
     */
    public function downloadArchive(string $url): void
    {
        $this->executionTime('report');
        $this->executionTime('step');
        try {
            $client = new Client(['timeout' => 3600]);
            $response = $client->get($url);

            $fileName = 'expedia_'.$this->type.'.gz';

            if ($response->getStatusCode() === 200) {
                $stream = $response->getBody();
                $filePath = $this->savePath.'/'.$fileName;
                $file = fopen($filePath, 'w');

                if ($file) {
                    while (! $stream->eof()) {
                        fwrite($file, $stream->read(1024));
                    }

                    fclose($file);

                    // Get the file size
                    $fileSize = filesize($filePath) / (1024 * 1024 * 1024);

                    $this->info('Step:2 download file '.$fileName.' in '.$this->executionTime('step').' seconds');

                    $this->saveSuccessReport('DownloadExpediaData', 'Step:2 download file', json_encode([
                        'path' => $this->savePath,
                        'fileName' => $fileName,
                        'execution_time' => $this->executionTime('report').' sec',
                        'fileSize' => $fileSize.' GB',
                    ]));
                } else {
                    $this->errorStep2($response);
                }
            } else {
                $this->errorStep2($response);
            }
        } catch (Exception|GuzzleException $e) {
            $this->error('Error downloading gz file:  '.$e->getMessage().' | '.$e->getTraceAsString());
            $this->saveErrorReport('DownloadExpediaData', 'Step:2 download File Gz', json_encode([
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));
        }
    }

    private function errorStep2($response): void
    {
        $this->error('Error downloading gz file:  '.json_encode([
            'response-status' => $response->status(),
            'response-body' => $response->body(),
            'path' => $this->savePath,
            'execution_time' => $this->executionTime('step').' sec',
        ]));
        $this->saveErrorReport('DownloadExpediaData', 'Step:2 download file', json_encode([
            'response-status' => $response->status(),
            'response-body' => $response->body(),
            'path' => $this->savePath,
            'execution_time' => $this->executionTime('report').' sec',
        ]));
    }

    /**
     * Unzip file
     */
    public function unzipFile(): void
    {
        $this->executionTime('report');
        $this->executionTime('step');
        try {
            $archive = $this->savePath.'/expedia_'.$this->type.'.gz';
            $result = Process::timeout(3600)->run('gunzip -f '.$archive);

            if ($result->successful()) {

                $decompressedFilePath = str_replace('.gz', '', $archive); // Get the path to the decompressed file
                $fileSize = filesize($decompressedFilePath) / (1024 * 1024 * 1024);

                $this->saveSuccessReport('DownloadExpediaData', 'Step:3 unzip file', json_encode([
                    'archive' => $archive,
                    'result' => $result,
                    'execution_time' => $this->executionTime('report').' sec',
                    'fileSize' => $fileSize.' GB',
                ]));
                $this->info('DownloadExpediaData Step:3 unzip file: '.json_encode([
                    'archive' => $archive,
                    'result' => $result,
                    'execution_time' => $this->executionTime('step').' sec',
                    'fileSize' => $fileSize.' GB',
                ]));
            } else {
                $this->error('Error DownloadExpediaData Step:3 unzip file:  '.json_encode([
                    'result' => $result->throw(),
                    'execution_time' => $this->executionTime('step').' sec',
                ]));
                $this->saveErrorReport('DownloadExpediaData', 'Step:3 unzip file', json_encode([
                    'getMessage' => $result->throw(),
                    'execution_time' => $this->executionTime('report').' sec',
                ]));
            }
        } catch (Exception $e) {
            $this->error('Error unzip file:  '.$e->getMessage().' | '.$e->getTraceAsString());
            $this->saveErrorReport('DownloadExpediaData', 'Step:3 unzip File', json_encode([
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));
        }
    }

    /**
     * Parse json to db
     */
    public function parseJsonToDb(): void
    {
        $this->executionTime('report');
        $this->executionTime('step');

        $filePath = $this->savePath.'/expedia_'.$this->type;

        // Open the JSONL file for reading
        $file = fopen($filePath, 'r');

        if (! $file) {
            $this->error('Unable to open the JSONL file.');

            return;
        }

        $batchSize = self::BATCH_SIZE;
        $batchData = [];
        $batchCount = 0;
        $arr_json = [
            'address', 'ratings', 'location',
        ];
        $arr = [
            'property_id', 'name', 'city', 'latitude', 'longitude', 'phone', 'total_occupancy',
        ];
        $arr_json_slave = [
            'category', 'business_model',
            'checkin', 'checkout', 'fees', 'policies', 'attributes', 'amenities',
            'images', 'onsite_payments', 'rooms', 'rates', 'dates', 'descriptions',
            'themes', 'chain', 'brand', 'statistics', 'vacation_rental_details',
            'airports', 'fax', 'spoken_languages', 'all_inclusive', 'rooms_occupancy',
        ];
        $arr_slave = [
            'expedia_property_id', 'fax', 'tax_id', 'rank', 'multi_unit',
            'payment_registration_recommended', 'supply_source',
        ];

        $propertyIds = [];
        while (($line = fgets($file)) !== false) {
            $data = json_decode($line, true);

            if($this->city && strtolower(Arr::get($data,'address.city')) !== $this->city )
            {
                continue;
            }

            $output = [];
            foreach ($arr_json as $key) {
                $output[$key] = json_encode(['']);
            }
            foreach ($arr as $key) {
                $output[$key] = '';
            }

            $outputSlave = [];
            foreach ($arr_json_slave as $key) {
                $outputSlave[$key] = json_encode(['']);
            }
            foreach ($arr_slave as $key) {
                $outputSlave[$key] = '';
            }

            if (! is_array($data)) {
                break;
            }

            $output['rating'] = 0;

            $is_write = true;

            foreach ($data as $key => $value) {

                $output['is_active'] = true;

                if ($key == 'property_id') {
                    $propertyIds[] = $value;
                    $outputSlave['expedia_property_id'] = $value;
                }
                if ($key == 'ratings') {
                    $output['rating'] = $value['property']['rating'] ?? 0;
                }
                if ($key == 'address') {
                    $output['city'] = $value['city'] ?? '';
                }
                if ($key == 'location') {
                    $output['latitude'] = $value['coordinates']['latitude'] ?? 0;
                    $output['longitude'] = $value['coordinates']['longitude'] ?? 0;
                }

                $total = 0;
                if ($key == 'rooms') {
                    $arr_rooms = [];
                    foreach ($value as $room) {
                        $arr_rooms[$room['id']] = [
                            'id' => $room['id'],
                            'occupancy' => $room['occupancy'],
                        ];
                        if ($total < $room['occupancy']['max_allowed']['total']) {
                            $total = $room['occupancy']['max_allowed']['total'];
                        }
                    }
                    $outputSlave['rooms_occupancy'] = json_encode($arr_rooms);
                    $output['total_occupancy'] = $total;
                }

                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if (in_array($key, $arr_json) || in_array($key, $arr)) {
                    $output[$key] = $value;
                }
                if (in_array($key, $arr_json_slave) || in_array($key, $arr_slave)) {
                    $outputSlave[$key] = $value;
                }
            }
            // dd($outputSlave, $output);

            $ratingConfig = GeneralConfiguration::latest()->first()->star_ratings;

            $rating = $ratingConfig ?? self::MIN_RATING ?? 3;
            // $rating = 0;

            if ($output['rating'] < $rating) {
                $is_write = false;
            }

            if ($is_write) {
                $batchData[] = $output;
                $batchDataSlave[] = $outputSlave;
            }

            // Check if we have accumulated enough data to insert as a batch
            if (count($batchData) >= $batchSize) {
                try {
                    DB::beginTransaction();

                    ExpediaContent::whereIn('property_id', $propertyIds)->delete();
                    ExpediaContent::insert($batchData);

                    ExpediaContentSlave::whereIn('expedia_property_id', $propertyIds)->delete();
                    ExpediaContentSlave::insert($batchDataSlave);

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->error('ImportJsonlData error'.$e->getMessage());
                    $this->saveErrorReport('DownloadExpediaData', 'Import Json lData', json_encode([
                        'getMessage' => $e->getMessage(),
                        'getTraceAsString' => $e->getTraceAsString(),
                        'batch' => $batchCount,
                        'countBatch' => count($batchData),
                        'execution_time' => $this->executionTime('report').' sec',
                    ]));
                }
                $batchCount++;
                $this->info('Data imported batchData: '.$batchCount.' count =  '.count($batchData));
                $batchData = [];
                $batchDataSlave = [];
                $propertyIds = [];
            }
        }

        // Insert any remaining data as the last batch
        if (! empty($batchData)) {
            try {
                DB::beginTransaction();

                ExpediaContent::whereIn('property_id', $propertyIds)->delete();
                ExpediaContent::insert($batchData);

                ExpediaContentSlave::whereIn('expedia_property_id', $propertyIds)->delete();
                ExpediaContentSlave::insert($batchDataSlave);

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->error('ImportJsonlData error'.$e->getMessage());
                $this->saveErrorReport('DownloadExpediaData', 'Step:4 Import Json to Data', json_encode([
                    'getMessage' => $e->getMessage(),
                    'getTraceAsString' => $e->getTraceAsString(),
                    'execution_time' => $this->executionTime('report').' sec',
                ]));
            }
        }

        fclose($file);

        if (env('APP_URL') !== 'http://localhost:8008') {
            unlink($filePath);
        }

        $this->info('Import completed. '.$this->executionTime('step').' seconds');

        $this->saveSuccessReport('DownloadExpediaData', 'Step:4 Import Json to Data', json_encode([
            'execution_time' => $this->executionTime('report').' sec',
        ]));
    }

    private function setInactiveStatus(): void
    {
        $this->executionTime('report');
        $this->executionTime('step');

        $headers = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $params = [
            'since' => Carbon::now()->format('Y-m-d'),
            'billing_terms' => $this->billingTerms,
            'payment_terms' => $this->paymentTerms,
            'partner_point_of_sale' => $this->partnerPointSale,
        ];
        $response = $this->rapidClient->get('v3/properties/inactive', $params, $headers);
        $dataResponse = json_decode($response->getBody()->getContents(), true);

        if (is_array($dataResponse)) {
            $propertyIds = [];
            foreach ($dataResponse as $item) {
                if (isset($item['property_id'])) {
                    $propertyIds[] = $item['property_id'];
                }
            }
        }

        try {
            DB::beginTransaction();
            ExpediaContent::whereIn('property_id', $propertyIds)
                ->update(['is_active' => false]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->error('SetInactiveStasus error'.$e->getMessage());
            $this->saveErrorReport('DownloadExpediaData', 'Step:5 Set Inactive Stasus', json_encode([
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report').' sec',
            ]));
        }

        $this->info('SetInactiveStasus. '.$this->executionTime('step').' seconds');

        $this->saveSuccessReport('DownloadExpediaData', 'Step:5 Set Inactive Stasus', json_encode([
            'execution_time' => $this->executionTime('report').' sec',
            'propertyIds_set_inactive' => $propertyIds,
        ]));
    }

    private function saveSuccessReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content, 'success');
    }

    private function saveErrorReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content);
    }

    private function saveReport(string $action, string $description, string $content, string $level = 'error'): void
    {
        $this->apiExceptionReport
            ->save(
                $this->report_id,
                $level,
                $this->expedia_id,
                $action,
                $description,
                $content
            );
    }
}
