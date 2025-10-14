<?php

namespace Modules\API\Suppliers\AirwallexSupplier;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AirwallexClient
{
    private Client $client;

    private string $clientId;

    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->client = new Client;
        $this->clientId = Config::get('airwallex.client_id');
        $this->apiKey = Config::get('airwallex.api_key');
        $this->baseUrl = Config::get('airwallex.base_url', 'https://api-demo.airwallex.com');
    }

    /**
     * Gen token auth Airwallex, cached
     * https://www.airwallex.com/docs/api#/Getting_Started
     */
    public function getToken(): ?string
    {
        $cacheKey = 'airwallex_api_token';
        $token = Cache::get($cacheKey);
        if ($token) {
            return $token;
        }
        $url = $this->baseUrl.'/api/v1/authentication/login';
        $headers = [
            'Content-Type' => 'application/json',
            'x-client-id' => $this->clientId,
            'x-api-key' => $this->apiKey,
        ];
        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $token = $data['token'] ?? null;
            if ($token) {
                // If expires_in is present, use it, otherwise default to 10 min
                $ttl = isset($data['expires_in']) ? ((int) $data['expires_in'] - 10) : 600;
                Cache::put($cacheKey, $token, $ttl);
            }

            return $token;
        } catch (Exception $e) {
            Log::error('Airwallex token error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get account balance
     * https://www.airwallex.com/docs/api#/Getting_Started
     */
    public function getBalance(): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/balances/current';
        $headers = [
            'Authorization' => 'Bearer '.$token,
        ];
        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex balance error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Create PaymentIntent
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents_create/post
     */
    public function createPaymentIntent(
        float $amount,
        string $currency,
        string $merchantOrderId,
        array $order,
        ?string $descriptor,
        ?string $returnUrl,
        array $metadata = [],
        array $direction = [],
        ?string $bookingId = null
    ): ?array {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/pa/payment_intents/create';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        $requestId = \Illuminate\Support\Str::uuid()->toString();
        $body = [
            'amount' => $amount,
            'currency' => $currency,
            'merchant_order_id' => $merchantOrderId,
            'order' => $order,
            'request_id' => $requestId,
            'direction' => $direction,
            'payment_method_options' => [
                'card' => [
                    'card_input_via' => 'ecommerce',
                ],
            ],
        ];

        if (! empty($descriptor)) {
            $body['descriptor'] = $descriptor;
        }
        if (! empty($returnUrl)) {
            $body['return_url'] = $returnUrl;
        }
        if (! empty($metadata)) {
            $body['metadata'] = $metadata;
        }

        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $body,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex createPaymentIntent error: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Retrieve a PaymentIntent
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents__id_/get
     */
    public function getPaymentIntent(string $paymentIntentId): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/pa/payment_intents/'.$paymentIntentId;
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex getPaymentIntent error: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Update a PaymentIntent
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents__id__update/post
     */
    public function updatePaymentIntent(string $paymentIntentId, array $payload): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/pa/payment_intents/'.$paymentIntentId.'/update';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $payload,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex updatePaymentIntent error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Confirm a PaymentIntent
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents__id__confirm/post
     */
    public function confirmPaymentIntent(string $paymentIntentId, array $payload): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/pa/payment_intents/'.$paymentIntentId.'/confirm';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $payload,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex confirmPaymentIntent error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Continue to confirm a PaymentIntent (for scenarios like 3DS, DCC, micro-deposits)
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents__id__confirm_continue/post
     */
    public function confirmContinuePaymentIntent(string $paymentIntentId, array $payload): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/pa/payment_intents/'.$paymentIntentId.'/confirm_continue';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $payload,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex confirmContinuePaymentIntent error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Capture a PaymentIntent
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents__id__capture/post
     */
    public function capturePaymentIntent(string $paymentIntentId, array $payload): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/pa/payment_intents/'.$paymentIntentId.'/capture';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $payload,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex capturePaymentIntent error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Cancel a PaymentIntent
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents__id__cancel/post
     */
    public function cancelPaymentIntent(string $paymentIntentId, array $payload): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $url = $this->baseUrl.'/api/v1/pa/payment_intents/'.$paymentIntentId.'/cancel';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $payload,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex cancelPaymentIntent error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get list of PaymentIntents
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents/get
     */
    public function getPaymentIntentsList(array $queryParams = []): ?array
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        $queryString = http_build_query($queryParams);
        $url = $this->baseUrl.'/api/v1/pa/payment_intents';
        if ($queryString) {
            $url .= '?'.$queryString;
        }
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        } catch (Exception $e) {
            Log::error('Airwallex getPaymentIntentsList error: '.$e->getMessage());

            return null;
        }
    }
}
