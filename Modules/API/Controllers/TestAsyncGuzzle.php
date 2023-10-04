<?php

namespace Modules\API\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\JsonResponse;
use Modules\API\BaseController;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class TestAsyncGuzzle extends BaseController
{
	protected $rapidClient;

	public function __construct(RapidClient $rapidClient) {
		$this->rapidClient = $rapidClient;
	}

	private array $testParams = [
		"checkin" => "2023-12-10",
		"checkout" => "2023-12-31",
		"occupancy" => "2",
		"property_id" => "12537922",
		"rate_plan_count" => 1,
		"language" => "en-US",
		"country_code" => "US",
		"currency" => "USD",
		"sales_channel" => "agent_tool",
		"sales_environment" => "hotel_package",
		"rate_option" => "member",
		"billing_terms" => "",
		"payment_terms" => "BASE_DIR",
		"partner_point_of_sale" => "B2B_EAC_BASE_DIR",
	];

	public function test() :JsonResponse
	{
		// Create a Guzzle HTTP client
		$client = new Client();

		// Define an array of URLs to make asynchronous requests
		$urls = [
			'https://jsonplaceholder.typicode.com/posts/1',
			'https://jsonplaceholder.typicode.com/posts/2',
			'https://jsonplaceholder.typicode.com/posts/3',
		];

		// Create an array to hold promises
		$promises = [];

		// Create promises for each URL
		foreach ($urls as $url) {
			$promises[] = $client->getAsync($url);
		}

		// Wait for all promises to complete asynchronously
		$responses = Promise\Utils::settle($promises)->wait();

		$res = [];
		// Handle responses
		foreach ($responses as $response) {
			if ($response['state'] === 'fulfilled') {
				$body = $response['value']->getBody();
				$res[] = $this->sendResponse(['Response' => $body->getContents()], 'success');
			} else {
				$reason = $response['reason'];
				\Log::error('Request failed' . $reason->getMessage());
				return $this->sendError(['Request failed' => $reason->getMessage()], 'falied');
			}
		}

		return $this->sendResponse(['count' => count($res), 'Response' => $res], 'success');
	}


	public function testSync()
	{
		try {		
			$res = $this->rapidClient->get("v3/properties/availability", $this->testParams);
			\Log::info(json_encode($res->getBody()->getContents()));
		} catch (\Exception $e) {
			\Log::error("testSync exception: " . $e->getMessage().  ' ' . $e->getTraceAsString());
		}
	}

	public function testAsync()
	{
		try {
			$promises = ["12537922" => $this->rapidClient->getAsync("v3/properties/availability", $this->testParams)];

			$resolvedResponses = Promise\Utils::settle($promises)->wait();

			foreach ($resolvedResponses as $response) {
				if ($response['state'] === 'fulfilled') {
					$data = $response['value']->getBody()->getContents();
					\Log::debug('PropertyPriceCall data ' . json_encode($data));
				} else {
					\Log::error('Promise failed: ' . $response['reason']->getMessage());
				}
			}
		} catch (\Exception $e) {
			\Log::error("testAsync exception: " . $e->getMessage().  ' ' . $e->getTraceAsString());
		}
	}
}