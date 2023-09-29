<?php

namespace Modules\API\ContentAPI\ExpediaSupplier;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class RapidClient
{
    // Base URL
    // private const RAPID_BASE_URL = env('EXPEDIA_RAPID_BASE_URL');
    private const RAPID_BASE_URL = "https://test.ean.com";

    // Headers
    private const GZIP = "gzip";
    private const AUTHORIZATION_HEADER = "EAN APIKey=%s,Signature=%s,timestamp=%s";

    private $apiKey;
    private $sharedSecret;
    private $client;

    public function __construct ($apiKey, $sharedSecret)
    {
        $this->apiKey = $apiKey;
        $this->sharedSecret = $sharedSecret;
        $this->client = new Client(); // Initialize the client in the constructor
    }

    public function get ($path, $queryParameters)
    {
        $queryParams = [];
        foreach ($queryParameters as $key => $value) {
            $queryParams[$key] = $value;
        }

        $url = self::RAPID_BASE_URL . '/' . $path . '?' . http_build_query($queryParams);

        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Accept-Encoding' => self::GZIP,
                'Authorization' => $this->generateAuthHeader()
            ]
        ]);

        return $response;
    }

    private function generateAuthHeader ()
    {
        $timeStampInSeconds = strval(time());
        $input = $this->apiKey . $this->sharedSecret . $timeStampInSeconds;
        $signature = hash('sha512', $input);

        return sprintf(self::AUTHORIZATION_HEADER, $this->apiKey, $signature, $timeStampInSeconds);
    }
}
