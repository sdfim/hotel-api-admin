<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface as promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;

class RapidClient
{
	// Headers
	private const GZIP = "gzip";
	private const AUTHORIZATION_HEADER = "EAN APIKey=%s,Signature=%s,timestamp=%s";

	private $apiKey;
	private $sharedSecret;
	private $client;
	private $rapidBaseUrl;

	public function __construct($apiKey, $sharedSecret)
	{
		$this->apiKey = $apiKey;
		$this->sharedSecret = $sharedSecret;
		$this->client = new Client(['debug' => fopen('./rapidClientDebug.log', 'w')]);
		$this->rapidBaseUrl = env('EXPEDIA_RAPID_BASE_URL');
	}

	public function get($path, $queryParameters, $addHeaders = [])
	{
		$queryParams = [];
		foreach ($queryParameters as $key => $value) {
			$queryParams[$key] = $value;
		}
		$url = $this->rapidBaseUrl . '/' . $path . '?' . http_build_query($queryParams);

		$headers = [
			'Authorization' => $this->generateAuthHeader(),
			'Accept-Encoding' => self::GZIP,
		];

		$request = new Request('GET', $url, $headers + $addHeaders);
		$res = $this->client->sendAsync($request)->wait();

		return $res;
	}

	public function put($path, $queryParameters, $body, $addHeaders = [])
	{
		$queryParams = [];
		foreach ($queryParameters as $key => $value) {
			$queryParams[$key] = $value;
		}
		$url = $this->rapidBaseUrl . '/' . $path . '?' . http_build_query($queryParams);

		$headers = [
			'Authorization' => $this->generateAuthHeader(),
			'Accept-Encoding' => self::GZIP,
		];
		$headers = $headers + $addHeaders;

		// dd($headers, $body);

		$request = new Request('PUT', $url, $headers, $body);
		$res = $this->client->sendAsync($request)->wait();

		return $res;
	}

	public function delete($path, $queryParameters, $body, $addHeaders = [])
	{
		$queryParams = [];
		foreach ($queryParameters as $key => $value) {
			$queryParams[$key] = $value;
		}
		$url = $this->rapidBaseUrl . '/' . $path . '?' . http_build_query($queryParams);

		$headers = [
			'Authorization' => $this->generateAuthHeader(),
			'Accept-Encoding' => self::GZIP,
		];

		$request = new Request('DELETE', $url, $headers + $addHeaders, $body);
		$res = $this->client->send($request);

		return $res;
	}

	public function post($path, $queryParameters, $body, $addHeaders = [])
	{
		$queryParams = [];
		foreach ($queryParameters as $key => $value) {
			$queryParams[$key] = $value;
		}
		$url = $this->rapidBaseUrl . '/' . $path . '?' . http_build_query($queryParams);

		$headers = [
			'Authorization' => $this->generateAuthHeader(),
			'Accept-Encoding' => self::GZIP,
		];

		$request = new Request('POST', $url, $headers + $addHeaders, $body);
		$res = $this->client->sendAsync($request)->wait();

		return $res;
	}

	private function generateAuthHeader()
	{
		$timeStampInSeconds = strval(time());
		$input = $this->apiKey . $this->sharedSecret . $timeStampInSeconds;
		$signature = hash('sha512', $input);

		return sprintf(self::AUTHORIZATION_HEADER, $this->apiKey, $signature, $timeStampInSeconds);
	}

	public function getAsync($path, $queryParameters, $addHeaders=[]): promise
	{
		foreach (range(0, 10) as $i) $arrayReplace[] = '%5B'.$i.'%5D';
		$http_build_query = http_build_query($queryParameters);
		$http_query = str_replace($arrayReplace, '', $http_build_query);

		$url = $this->rapidBaseUrl . '/' . $path . '?' . $http_query;

		$headers = [
			'Accept-Encoding' => self::GZIP,
			'Authorization' => $this->generateAuthHeader()
		];
		$request = new Request('GET', $url, $headers + $addHeaders);
		try {
			$res = $this->client->sendAsync($request);
		} catch (\Exception $e) {
			\Log::error('Error while creating promise: ' . $e->getMessage());
		}

		return $res;
	}

}
