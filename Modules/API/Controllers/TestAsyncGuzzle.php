<?php

namespace Modules\API\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\JsonResponse;
use Modules\API\BaseController;

class TestAsyncGuzzle extends BaseController
{

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


}