<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\XMLData;
use SimpleXMLElement;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;


class ImportXMLDataGiataGeography extends Command
{
    protected $signature = 'import_giata_geography:xml {url}';
    protected $description = 'Import XML data from a URL';

    public function __construct()
    {
        parent::__construct();
    }


public function handle()
{
    $xmlContent = file_get_contents('./xmlObject.xml');
	$xml = simplexml_load_string($xmlContent);
	$json = json_encode($xml);
	$phpObj = json_decode($json, true);

	// $flattened = Arr::dot($phpObj);
	// dd($flattened);

	foreach ($phpObj as $channel) {
		// $code = $channel['Code'];
		foreach ($channel as $key => $value) {
			// $code = $channel['Code'];
			foreach ($value as $key => $v) {
				// $code = $channel['Code'];
				dump($key, $v);
				if ($key == 5) dd();
			}
		}
		dd($channel, count($channel), count($phpObj));
		// XMLData::create([
		//     'xml_content' => $node->asXML()
		// ]);
	}

	
	$url = $this->argument('url') ?? 'http://tticodes.giatamedia.com/webservice/rest/1.0/geography';
    
    // Define your HTTP authentication credentials
    $username = 'tticodes@godigitaldevelopment.com';
    $password = 'aw4ZD8ky';

    try {
        // Create a Guzzle HTTP client instance
        $client = new Client([
            'auth' => [$username, $password],
        ]);

        // Send an HTTP GET request with authentication
        $response = $client->get($url);

        if ($response->getStatusCode() === 200) {
            // Get the XML content from the response body
            $xmlContent = $response->getBody()->getContents();

            // Parse the XML content
            $xmlObject = new SimpleXMLElement($xmlContent);

			// dump($xmlObject);
			
			/*
			$xmlString = $xmlObject->asXML();
			$filePath = './xmlObject.xml';
			file_put_contents($filePath, $xmlString);
			// Check if the write was successful
			if (file_exists($filePath)) {
				echo "XML data has been written to $filePath successfully.";
			} 
			*/

			$xml = simplexml_load_string($xmlContent);
			$json = json_encode($xml);
			$phpObj = json_decode($json);
			
            foreach ($phpObj as $channel) {
				// $code = $channel['Code'];
				dd($channel, count((array)$phpObj));
                // XMLData::create([
                //     'xml_content' => $node->asXML()
                // ]);
            }

            $this->info('XML data imported successfully');
        } else {
            $this->error('Error importing XML data. HTTP status code: ' . $response->getStatusCode());
        }
    } catch (Exception $e) {
        $this->error('Error importing XML data: ' . $e->getMessage());
    }
}

}
