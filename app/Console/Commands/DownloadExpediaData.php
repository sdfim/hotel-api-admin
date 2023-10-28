<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use App\Models\ExpediaContent;
use Modules\Inspector\ExceptionReportController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Process;
use GuzzleHttp\Client;
use App\Models\GeneralConfiguration;

class DownloadExpediaData extends Command
{

	use BaseTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download-expedia-data {type} {step}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var RapidClient
     */
    protected RapidClient $rapidClient;
    /**
     * @var ExceptionReportController
     */
    protected ExceptionReportController $apiExceptionReport;
    /**
     *
     */
    private const PROPERTY_CONTENT_PATH = "v3/files/properties/";
    /**
     *
     */
    private const BATCH_SIZE = 100;
    /**
     *
     */
    private const MIN_RATING = 4;
    /**
     * @var string|null
     */
    private string|null $type;
    /**
     * @var string|null
     */
    private string|null $step;

    /**
     * @var int
     */
    protected int $expedia_id;
    /**
     * @var string|null
     */
    protected string|null $report_id;
    /**
     * @var string
     */
    protected string $savePath;

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
        // TODO: get expedia_id from suppliers table
        $this->expedia_id = 1;
        $this->current_time['main'] = microtime(true);
        $this->current_time['step'] = microtime(true);
        $this->current_time['report'] = microtime(true);
        $this->savePath = storage_path() . '/app';
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(): void
    {
        $this->type = $this->argument('type'); // content
        $this->step = $this->argument('step'); // 1, 2, 3, 4
        $this->report_id = Str::uuid()->toString();

        $this->executionTime('report');

        $result = Process::run('df -h')->output();
        $this->info('DownloadExpediaData df -h: ' . $result);
        $result = Process::run('free -h')->output();
        $this->info('DownloadExpediaData free -h: ' . $result);

        if (str_contains($this->step, 1)) {
            # get url from expedia
            $url = $this->getUrlArchive();
            $this->info('url from expedia ' . $url . ' in ' . $this->executionTime('step') . ' seconds');

            $this->saveSuccessReport('DownloadExpediaData', 'Step:1 get url', json_encode([
                'url' => $url,
                'execution_time' => $this->executionTime('report') . ' sec',
            ]));
        }

        if (str_contains($this->step, 2)) {
            # download file from url and save to storage
            $this->downloadArchive($url);
        }

        if (str_contains($this->step, 3)) {
            # unzip file
            $this->unzipFile();
        }

        if (str_contains($this->step, 4)) {
            # parse json to db
            $this->parseJsonToDb();
        }
    }

    /**
     * Get url from expedia
     * @return string
     */
    function getUrlArchive(): string
    {
        $this->executionTime('report');
        $queryParams = [
            'language' => 'en-US',
            'supply_source' => 'expedia',
        ];
        try {
            $response = $this->rapidClient->get(self::PROPERTY_CONTENT_PATH . $this->type, $queryParams);

            $propertyContents = $response->getBody()->getContents();
            $url = json_decode($propertyContents, true)['href'];
        } catch (Exception $e) {
            $this->saveErrorReport('DownloadExpediaData', 'getUrlArchive', json_encode([
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report') . ' sec',
            ]));
        }

        return $url;
    }

    /**
     * Download file from url and save to storage
     * @param string $url
     * @return void
     */
    function downloadArchive(string $url): void
    {
        $this->executionTime('report');
        $this->executionTime('step');
        try {
            $client = new Client(['timeout' => 3600]);
            $response = $client->get($url);

            $fileName = 'expedia_' . $this->type . '.gz';

            if ($response->getStatusCode() === 200) {
                $stream = $response->getBody();
                $file = fopen($this->savePath . '/' . $fileName, 'w');

                if ($file) {
                    while (!$stream->eof()) {
                        fwrite($file, $stream->read(1024));
                    }

                    fclose($file);

                    $this->info('Step:2 download file ' . $fileName . ' in ' . $this->executionTime('step') . ' seconds');

                    $this->saveSuccessReport('DownloadExpediaData', 'Step:2 download file', json_encode([
                        'path' => $this->savePath,
                        'fileName' => $fileName,
                        'execution_time' => $this->executionTime('report') . ' sec',
                    ]));
                } else {
                    $this->errorStep2($response);
                }
            } else {
                $this->errorStep2($response);
            }
        } catch (Exception|GuzzleException $e) {
            $this->error('Error downloading gz file:  ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            $this->saveErrorReport('DownloadExpediaData', 'Step:2 download File Gz', json_encode([
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report') . ' sec',
            ]));
        }
    }

    /**
     * @param $response
     * @return void
     */
    private function errorStep2($response): void
    {
        $this->error('Error downloading gz file:  ' . json_encode([
                'response-status' => $response->status(),
                'response-body' => $response->body(),
                'path' => $this->savePath,
                'execution_time' => $this->executionTime('step') . ' sec',
            ]));
        $this->saveErrorReport('DownloadExpediaData', 'Step:2 download file', json_encode([
            'response-status' => $response->status(),
            'response-body' => $response->body(),
            'path' => $this->savePath,
            'execution_time' => $this->executionTime('report') . ' sec',
        ]));
    }

    /**
     * Unzip file
     * @return void
     */
    function unzipFile(): void
    {
        $this->executionTime('report');
        $this->executionTime('step');
        try {
            $archive = $this->savePath . '/expedia_' . $this->type . '.gz';
            $result = Process::timeout(3600)->run('gunzip -f ' . $archive);

            if ($result->successful()) {
                $this->saveSuccessReport('DownloadExpediaData', 'Step:3 unzip file', json_encode([
                    'archive' => $archive,
                    'result' => $result,
                    'execution_time' => $this->executionTime('report') . ' sec',
                ]));
                $this->info('DownloadExpediaData Step:3 unzip file: ' . json_encode([
                        'archive' => $archive,
                        'result' => $result,
                        'execution_time' => $this->executionTime('step') . ' sec',
                    ]));
            } else {
                $this->error('Error DownloadExpediaData Step:3 unzip file:  ' . json_encode([
                        'result' => $result->throw(),
                        'execution_time' => $this->executionTime('step') . ' sec',
                    ]));
                $this->saveErrorReport('DownloadExpediaData', 'Step:3 unzip file', json_encode([
                    'getMessage' => $result->throw(),
                    'execution_time' => $this->executionTime('report') . ' sec',
                ]));
            }
        } catch (Exception $e) {
            $this->error('Error unzip file:  ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            $this->saveErrorReport('DownloadExpediaData', 'Step:3 unzip File', json_encode([
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'execution_time' => $this->executionTime('report') . ' sec',
            ]));
        }
    }

    /**
     * Parse json to db
     * @return void
     */
    function parseJsonToDb(): void
    {
        $this->executionTime('report');
        $this->executionTime('step');

		$filePath = $this->savePath . '/expedia_' . $this->type;

        // Open the JSONL file for reading
        $file = fopen($filePath, 'r');

        if (!$file) {
            $this->error('Unable to open the JSONL file.');
            return;
        }

        $batchSize = self::BATCH_SIZE;
        $batchData = [];
        $batchCount = 0;
        $arr_json = [
            'address', 'ratings', 'location', 'category', 'business_model',
            'checkin', 'checkout', 'fees', 'policies', 'attributes', 'amenities',
            'images', 'onsite_payments', 'rooms', 'rates', 'dates', 'descriptions',
            'themes', 'chain', 'brand', 'statistics', 'vacation_rental_details',
            'airports', 'fax', 'spoken_languages', 'all_inclusive', 'rooms_occupancy',
        ];
        $arr = [
            'property_id', 'name', 'phone', 'tax_id', 'rank', 'multi_unit',
            'payment_registration_recommended', 'supply_source',
            'city', 'state_province_code', 'state_province_name',
            'postal_code', 'country_code', 'latitude', 'longitude', 'category_name',
            'checkin_time', 'checkout_time', 'total_occupancy',
        ];

		$propertyIds = [];
		while (($line = fgets($file)) !== false) {
			// Parse JSON from each line
			$data = json_decode($line, true);

            $output = [];
            foreach ($arr_json as $key) {
                $output[$key] = json_encode(['']);
            }
            foreach ($arr as $key) {
                $output[$key] = '';
            }

            if (!is_array($data)) break;

            $output['rating'] = 0;

            $is_write = true;

			foreach ($data as $key => $value) {

                if ($key == 'property_id') {
                    $propertyIds[] = $value;
                }
                if ($key == 'ratings') {
                    $output['rating'] = $value['property']['rating'] ?? 0;
                }
                if ($key == 'address') {
                    $output['city'] = $value['city'] ?? '';
                    $output['state_province_code'] = $value['state_province_code'] ?? '';
                    $output['state_province_name'] = $value['state_province_name'] ?? '';
                    $output['postal_code'] = $value['postal_code'] ?? '';
                    $output['country_code'] = $value['country_code'] ?? '';
                }
                if ($key == 'location') {
                    $output['latitude'] = $value['coordinates']['latitude'] ?? 0;
                    $output['longitude'] = $value['coordinates']['longitude'] ?? 0;
                }
                if ($key == 'category') {
                    $output['category_name'] = $value['name'] ?? '';
                }
                if ($key == 'checkin') {
                    $output['checkin_time'] = isset($value['begin_time']) ?
                        date('Y-m-d H:i:s', strtotime($value['begin_time'])) :
                        date('Y-m-d H:i:s', strtotime('00:00:00'));
                }
                if ($key == 'checkout') {
                    $output['checkout_time'] = $value['time'] ?
                        date('Y-m-d H:i:s', strtotime($value['time'])) :
                        date('Y-m-d H:i:s', strtotime('00:00:00'));
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
                    $output['rooms_occupancy'] = json_encode($arr_rooms);
                    $output['total_occupancy'] = $total;
                }

                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $output[$key] = $value;
            }

			$ratingConfig = GeneralConfiguration::latest()->first()->star_ratings;

			$rating = $ratingConfig ?? self::MIN_RATING ?? 4;

            if ($output['rating'] < $rating) $is_write = false;

            if ($is_write) $batchData[] = $output;

			// Check if we have accumulated enough data to insert as a batch
			if (count($batchData) >= $batchSize) {
				try {
					DB::beginTransaction();
					ExpediaContent::whereIn('property_id', $propertyIds)->delete();
					ExpediaContent::insert($batchData);
					DB::commit(); 
				} catch (Exception $e) {
					DB::rollBack();
					$this->error('ImportJsonlData error' . $e->getMessage());
					$this->saveErrorReport('DownloadExpediaData', 'Import Json lData', json_encode([
						'getMessage' => $e->getMessage(),
						'getTraceAsString' => $e->getTraceAsString(),
						'batch' => $batchCount,
						'countBatch' => count($batchData),
						'execution_time' => $this->executionTime('report') . ' sec',
					]));
				}
				$batchCount++;
				$this->info('Data imported batchData: ' . $batchCount . ' count =  ' . count($batchData));
				$batchData = [];
				$propertyIds = [];
			}
		}

        // Insert any remaining data as the last batch
        if (!empty($batchData)) {
            try {
				DB::beginTransaction();
                ExpediaContent::whereIn('property_id', $propertyIds)->delete();
                ExpediaContent::insert($batchData);
				DB::commit(); 
            } catch (Exception $e) {
				DB::rollBack();
                $this->error('ImportJsonlData error' . $e->getMessage());
                $this->saveErrorReport('DownloadExpediaData', 'Step:4 Import Json to Data', json_encode([
                    'getMessage' => $e->getMessage(),
                    'getTraceAsString' => $e->getTraceAsString(),
                    'execution_time' => $this->executionTime('report') . ' sec',
                ]));
            }
        }

        fclose($file);

		if (env('APP_URL') !== 'http://localhost:8008') unlink($filePath);

        $this->info('Import completed. ' . $this->executionTime('step') . " seconds");

        $this->saveSuccessReport('DownloadExpediaData', 'Step:4 Import Json to Data', json_encode([
            'execution_time' => $this->executionTime('report') . ' sec',
        ]));
    }

	/**
     * @param string $action
     * @param string $description
     * @param string $content
     * @return void
     */
    private function saveSuccessReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content, 'success');
    }

    /**
     * @param string $action
     * @param string $description
     * @param string $content
     * @return void
     */
    private function saveErrorReport(string $action, string $description, string $content): void
    {
        $this->saveReport($action, $description, $content);
    }

    /**
     * @param string $action
     * @param string $description
     * @param string $content
     * @param string $level
     * @return void
     */
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
