<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use App\Repositories\ConfigRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface as promise;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class RapidClient
{
    private const GZIP = "gzip";

    private const AUTHORIZATION_HEADER = "EAN APIKey=%s,Signature=%s,timestamp=%s";

    /** * @var Client */
    private Client $client;

    /** * @var Credentials */
    private Credentials $credentials;

    public function __construct()
    {
        $this->credentials = CredentialsFactory::fromConfig();
        $this->client = new Client();
    }

    /**
     * @param string $path
     * @param array $queryParameters
     * @param array $addHeaders
     * @return mixed
     */
    public function get(string $path, array $queryParameters, array $addHeaders = []): mixed
    {
        $queryParams = [];
        foreach ($queryParameters as $key => $value) {
            $queryParams[$key] = $value;
        }
        $url = $this->credentials->rapidBaseUrl . '/' . $path . '?' . http_build_query($queryParams);

        $headers = [
            'Authorization' => $this->generateAuthHeader(),
            'Accept-Encoding' => self::GZIP,
        ];

        $request = new Request('GET', $url, $headers + $addHeaders);
        return $this->client->sendAsync($request)->wait();
    }

    /**
     * @param string $path
     * @param array $queryParameters
     * @param string $body
     * @param array $addHeaders
     * @return mixed
     */
    public function put(string $path, array $queryParameters, string $body, array $addHeaders = []): mixed
    {
        $queryParams = [];
        foreach ($queryParameters as $key => $value) {
            $queryParams[$key] = $value;
        }
        $url = $this->credentials->rapidBaseUrl . '/' . $path . '?' . http_build_query($queryParams);

        $headers = [
            'Authorization' => $this->generateAuthHeader(),
            'Accept-Encoding' => self::GZIP,
        ];
        $headers = $headers + $addHeaders;

        $request = new Request('PUT', $url, $headers, $body);
        return $this->client->sendAsync($request)->wait();
    }

    /**
     * @param string $path
     * @param array $queryParameters
     * @param string $body
     * @param array $addHeaders
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function delete(string $path, array $queryParameters, string $body, array $addHeaders = []): ResponseInterface
    {
        $queryParams = [];

        foreach ($queryParameters as $key => $value) {
            $queryParams[$key] = $value;
        }
        $url = $this->credentials->rapidBaseUrl . $path . '?' . http_build_query($queryParams);
        $headers = [
            'Authorization' => $this->generateAuthHeader(),
            'Accept-Encoding' => self::GZIP,
        ];

        $request = new Request('DELETE', $url, $headers + $addHeaders, $body);

        return $this->client->send($request);
    }

    /**
     * @param string $path
     * @param array $queryParameters
     * @param string $body
     * @param array $addHeaders
     * @return mixed
     */
    public function post(string $path, array $queryParameters, string $body, array $addHeaders = []): mixed
    {
        $queryParams = [];
        foreach ($queryParameters as $key => $value) {
            $queryParams[$key] = $value;
        }
        $url = $this->credentials->rapidBaseUrl . '/' . $path . '?' . http_build_query($queryParams);

        $headers = [
            'Authorization' => $this->generateAuthHeader(),
            'Accept-Encoding' => self::GZIP,
        ];

        $request = new Request('POST', $url, $headers + $addHeaders, $body);
        return $this->client->sendAsync($request)->wait();
    }

    /**
     * @param string $path
     * @param array $queryParameters
     * @param array $addHeaders
     * @return promise
     */
    public function getAsync(string $path, array $queryParameters, array $addHeaders = []): promise
    {
        foreach (range(0, 250) as $i) $arrayReplace[] = '%5B' . $i . '%5D';
        $http_build_query = http_build_query($queryParameters);
        $http_query = str_replace($arrayReplace, '', $http_build_query);

        $url = $this->credentials->rapidBaseUrl . '/' . $path . '?' . $http_query;

        $headers = [
            'Accept-Encoding' => self::GZIP,
            'Authorization' => $this->generateAuthHeader()
        ];
        $request = new Request('GET', $url, $headers + $addHeaders);
        try {
            $res = $this->client->sendAsync($request, ['timeout' => ConfigRepository::getTimeout()]);
        } catch (Exception $e) {
            Log::error('Error while creating promise: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return $res;
    }

    /**
     * @return string
     */
    private function generateAuthHeader(): string
    {
        $timeStampInSeconds = strval(time());
        $input = $this->credentials->apiKey  . $this->credentials->sharedSecret  . $timeStampInSeconds;
        $signature = hash('sha512', $input);

        return sprintf(self::AUTHORIZATION_HEADER, $this->credentials->apiKey , $signature, $timeStampInSeconds);
    }
}
