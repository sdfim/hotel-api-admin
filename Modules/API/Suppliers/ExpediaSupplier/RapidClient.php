<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface as promise;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class RapidClient
{
    // Headers
    /**
     *
     */
    private const GZIP = "gzip";
    /**
     *
     */
    private const AUTHORIZATION_HEADER = "EAN APIKey=%s,Signature=%s,timestamp=%s";

    /**
     * @var string
     */
    private string $apiKey;
    /**
     * @var string
     */
    private string $sharedSecret;
    /**
     * @var Client
     */
    private Client $client;
    /**
     * @var string|mixed
     */
    private string $rapidBaseUrl;

    /**
     * @param $apiKey
     * @param $sharedSecret
     */
    public function __construct($apiKey, $sharedSecret)
    {
        $this->apiKey = $apiKey;
        $this->sharedSecret = $sharedSecret;
        $this->client = new Client(['debug' => fopen('./rapidClientDebug.log', 'w')]);
        $this->rapidBaseUrl = env('EXPEDIA_RAPID_BASE_URL');
    }

    /**
     * @param $path
     * @param $queryParameters
     * @param array $addHeaders
     * @return mixed
     */
    public function get($path, $queryParameters, array $addHeaders = []): mixed
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
        return $this->client->sendAsync($request)->wait();
    }

    /**
     * @param $path
     * @param $queryParameters
     * @param $body
     * @param array $addHeaders
     * @return mixed
     */
    public function put($path, $queryParameters, $body, array $addHeaders = []): mixed
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

        $request = new Request('PUT', $url, $headers, $body);
        return $this->client->sendAsync($request)->wait();
    }

    /**
     * @param $path
     * @param $queryParameters
     * @param $body
     * @param array $addHeaders
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function delete($path, $queryParameters, $body, array $addHeaders = []): ResponseInterface
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
        return $this->client->send($request);
    }

    /**
     * @param $path
     * @param $queryParameters
     * @param $body
     * @param array $addHeaders
     * @return mixed
     */
    public function post($path, $queryParameters, $body, array $addHeaders = []): mixed
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
        return $this->client->sendAsync($request)->wait();
    }

    /**
     * @return string
     */
    private function generateAuthHeader(): string
    {
        $timeStampInSeconds = strval(time());
        $input = $this->apiKey . $this->sharedSecret . $timeStampInSeconds;
        $signature = hash('sha512', $input);

        return sprintf(self::AUTHORIZATION_HEADER, $this->apiKey, $signature, $timeStampInSeconds);
    }

    /**
     * @param $path
     * @param $queryParameters
     * @param array $addHeaders
     * @return promise
     */
    public function getAsync($path, $queryParameters, array $addHeaders = []): promise
    {
        foreach (range(0, 10) as $i) $arrayReplace[] = '%5B' . $i . '%5D';
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
        } catch (Exception $e) {
            \Log::error('Error while creating promise: ' . $e->getMessage());
        }

        return $res;
    }
}
