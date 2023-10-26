<?php

namespace App\Console\Commands;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use App\Models\GiataProperty;
use Exception;
use GuzzleHttp\Client;

class DownloadGiataData extends Command
{
    /**
     * @var string
     */
    protected $signature = 'download-giata-data';

    /**
     * @var string
     */
    protected $description = 'Import XML data from a URL, wrtite to DB';

    /**
     * @var float|string
     */
    protected float|string $current_time;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        GiataProperty::truncate();

        $this->current_time = microtime(true);

        $url = 'http://tticodes.giatamedia.com/webservice/rest/1.0/properties';
        $batch = 1;

        // Define your HTTP authentication credentials
        $username = 'tticodes@godigitaldevelopment.com';
        $password = 'aw4ZD8ky';

        // Create a Guzzle HTTP client instance
        $client = new Client([
            'auth' => [$username, $password],
        ]);

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

                    $this->info('parseXMLToDb BATCH: ' . $batch . ' in ' . $this->executionTime() . ' seconds');

                    $batch++;

                    $this->info('XML data imported successfully, BATCH: ' . $batch);
                } else {
                    $this->error('Error importing XML data. HTTP status code: ' . $response->getStatusCode());
                }
            } catch (Exception|GuzzleException $e) {
                $this->error('Error importing XML data: ' . $e->getMessage());
            }
        }
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
        } catch (Exception $e) {
            $this->error('Error get url or it not exist: ' . $e->getMessage());
            return false;
        }

        $xml = simplexml_load_string($xmlContent);
        $json = json_encode($xml);

        $phpObj = json_decode($json, true);

        $this->info('parseXMLToDb, count: ' . count($phpObj['TTI_Property']) . ', url ' . $url);

        foreach ($phpObj['TTI_Property'] as $data) {
            $data = [
                'code' => $data["@attributes"]["Code"],
                'last_updated' => $data["@attributes"]["LastUpdated"],
                'name' => $data["Name"],
                'chain' => isset($data["Chain"]) ? json_encode($data["Chain"]) : null,
                'city' => $data["City"],
                'locale' => $data["Locale"],
                'address' => json_encode($data["Address"]),
                'phone' => isset($data["Phone"]) ? json_encode($data["Phone"]) : null,
                'position' => isset($data["Position"]) ? json_encode($data["Position"]) : null,
                'url' => isset($data["URL"]) ? json_encode($data["URL"]) : null,
                'cross_references' => json_encode($data["CrossReferences"]["CrossReference"]),
            ];
            $batchData[] = $data;
        }

        try {
            GiataProperty::insert($batchData);
        } catch (Exception $e) {
            \Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
            return false;
        }

        return $url;
    }
}
