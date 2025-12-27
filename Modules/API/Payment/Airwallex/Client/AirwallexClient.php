<?php

namespace Modules\API\Payment\Airwallex\Client;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
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
     * Create Customer
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Customers/_api_v1_pa_customers_create/post
     *
     * @param  string  $merchantCustomerId  Уникальный ID клиента из вашей системы (обязательно).
     * @param  string  $name  Имя клиента.
     * @param  string  $email  Email клиента.
     * @return array<array, array> [responseData, requestBody]
     *
     * @throws GuzzleException
     */
    public function createCustomer(string $merchantCustomerId, string $name, string $email): array
    {
        $token = $this->getToken();

        $url = $this->baseUrl.'/api/v1/pa/customers/create';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        $requestId = \Illuminate\Support\Str::uuid()->toString();

        $body = [
            'request_id' => $requestId,
            'merchant_customer_id' => $merchantCustomerId,
            'name' => $name,
            'email' => $email,
            'type' => 'INDIVIDUAL',
        ];

        if (! $token) {
            return [['error' => 'Unable to get API token'], $body];
        }

        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $body,
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() >= 400) {
                $errorData = json_decode($response->getBody()->getContents(), true);
                // Логируем точную ошибку
                Log::error('Airwallex createCustomer API error: '.json_encode($errorData));
                Log::error('2 Airwallex createCustomer API error: ', [
                    '$body' => $body,
                    '$response' => $response,
                    'response_status' => $response->getStatusCode(),
                    'response_body' => $errorData,
                ]);

                return [$errorData, $body];
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return [$data, $body];
        } catch (Exception $e) {
            Log::error('Airwallex createCustomer exception: '.$e->getMessage());

            return [['error' => $e->getMessage()], $body];
        }
    }

    /**
     * Create Payment Consent (for Merchant-Initiated Transactions)
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Consents/_api_v1_pa_payment_consents_create/post
     *
     * @param  string  $customerId  ID клиента Airwallex (cus_...).
     * @param  string  $merchantOrderId  ID заказа/бронирования в вашей системе.
     * @return array<array, array> [responseData, requestBody]
     *
     * @throws GuzzleException
     */
    public function createPaymentConsent(string $customerId, string $merchantOrderId): array
    {
        $token = $this->getToken();

        $url = $this->baseUrl.'/api/v1/pa/payment_consents/create';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];
        $requestId = \Illuminate\Support\Str::uuid()->toString();

        $body = [
            'request_id' => $requestId,
            'customer_id' => $customerId,
            'merchant_order_id' => $merchantOrderId, // Привязка к конкретному заказу
            'payment_method_type' => 'card', // Мы хотим сохранить карту
            'type' => 'recurring', // Тип: повторяющийся платеж
            'next_triggered_by' => 'merchant', // КЛЮЧЕВОЙ параметр для MoFoF (списание остатка)
            'mandate_details' => [
                // Обязательное описание того, на что дается согласие
                'description' => 'Consent for subsequent payment of the trip balance.',
                // 'agreement_type' => 'contract', // Можно добавить, если применимо
            ],
        ];

        if (! $token) {
            return [['error' => 'Unable to get API token'], $body];
        }

        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $body,
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() >= 400) {
                $errorData = json_decode($response->getBody()->getContents(), true);
                Log::error('Airwallex createPaymentConsent API error: '.json_encode($errorData));

                return [$errorData, $body];
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return [$data, $body];
        } catch (Exception $e) {
            Log::error('Airwallex createPaymentConsent exception: '.$e->getMessage());

            return [['error' => $e->getMessage()], $body];
        }
    }

    // В Modules\API\Suppliers\AirwallexSupplier\AirwallexClient.php

    /**
     * Get Payment Consent by ID
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Consents/_api_v1_pa_payment_consents__id_/get
     *
     * @param  string  $consentId  ID согласия на платеж (payment_consent_id).
     * @return array<array, array> [responseData, requestBody]
     *
     * @throws GuzzleException
     */
    public function retrievePaymentConsent(string $consentId): array
    {
        $token = $this->getToken();
        if (! $token) {
            return [['error' => 'Unable to get API token'], ['consentId' => $consentId]];
        }

        $url = $this->baseUrl.'/api/v1/pa/payment_consents/'.$consentId;

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() >= 400) {
                $errorData = json_decode($response->getBody()->getContents(), true);
                \Illuminate\Support\Facades\Log::error('Airwallex retrievePaymentConsent API error: '.json_encode($errorData));

                return [$errorData, ['consentId' => $consentId]];
            }

            $rs = json_decode($response->getBody()->getContents(), true);

            return [$rs, ['consentId' => $consentId]];
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Airwallex retrievePaymentConsent exception: '.$e->getMessage());

            return [['error' => $e->getMessage()], ['consentId' => $consentId]];
        }
    }

    /**
     * Create Payment Intent.
     *
     * @return array<array, array> [responseData, requestBody]
     *
     * @throws GuzzleException
     */
    public function createPaymentIntent(array $data): array
    {
        $token = $this->getToken();

        $amount = $data['amount'];
        $currency = $data['currency'];
        $merchantOrderId = $data['merchant_order_id'];
        $order = $data['order'];
        $descriptor = $data['descriptor'] ?? null;
        $returnUrl = $data['return_url'] ?? null;
        $customerId = $data['customer_id'] ?? null;
        $paymentConsentId = $data['payment_consent_id'] ?? null;
        $metadata = $data['metadata'] ?? null;
        $parsedUrl = parse_url($returnUrl);
        $host = Arr::get($parsedUrl, 'host');
        $requestOrigin = $host ? 'https://'.$host : 'https://fora-b2b-react-henna.vercel.app';

        $requestId = \Illuminate\Support\Str::uuid()->toString();

        $url = $this->baseUrl.'/api/v1/pa/payment_intents/create';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'merchant_order_id' => $merchantOrderId,
            'order' => $order,
            'payment_method_type' => ['card'],
            'request_id' => $requestId,
            'request_origin' => $requestOrigin,
            'payment_method_options' => [
                'card' => [
                    'auto_capture' => true,
                    'card_input_via' => 'ecommerce',
                ],
            ],
        ];

        if (! $token) {
            return [['error' => 'Unable to get API token'], $payload];
        }

        if ($paymentConsentId) {
            $payload['payment_consent_id'] = $paymentConsentId;
        }
        if ($customerId) {
            $payload['customer_id'] = $customerId;
            $payload['payment_method_type'] = ['card'];
        }
        if (! empty($descriptor)) {
            $payload['descriptor'] = $descriptor;
        }
        if (! empty($returnUrl)) {
            $payload['return_url'] = $returnUrl;
        }
        if (! empty($metadata)) {
            $payload['metadata'] = $metadata;
        }

        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $payload,
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() >= 400) {
                $errorData = json_decode($response->getBody()->getContents(), true);
                Log::error('Airwallex createPaymentIntent API error: '.json_encode($errorData),
                    [
                        '$payload' => $payload,
                        'response_status' => $response->getStatusCode(),
                        'response_body' => $errorData,
                    ]);

                return [$errorData, $payload];
            }

            $rs = json_decode($response->getBody()->getContents(), true);

            return [$rs, $payload];
        } catch (Exception $e) {
            Log::error('Airwallex createPaymentIntent exception: '.$e->getMessage(), ['$payload' => $payload]);
            $msg = $e->getMessage();

            // Пытаемся вытащить JSON из строки
            if (preg_match('/\{.*\}/s', $msg, $match)) {
                $json = json_decode($match[0], true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return [['error' => $json], $payload];
                }
            }

            // fallback: обычная строка
            return [['error' => $msg], $payload];
        }
    }

    /**
     * Confirm Payment Intent with Consent.
     * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/_api_v1_pa_payment_intents__id__confirm/post
     *
     * @return array<array, array> [responseData, requestBody]
     * @throws GuzzleException
     */
    public function confirmPaymentIntentWithConsent(string $intentId, array $payload): array
    {
        $token = $this->getToken();
        if (! $token) {
            return [['error' => 'Unable to get API token'], $payload];
        }
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ];

        $url = $this->baseUrl.'/api/v1/pa/payment_intents/'.$intentId.'/confirm';
        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $payload,
                'timeout' => 10,
            ]);

            $rs = json_decode($response->getBody()->getContents(), true);

            logger('Airwallex confirmPaymentIntentWithConsent response: ', ['response' => $rs, 'payload' => $payload]);

            return [$rs, $payload];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('ClientException: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'response' => $responseBody,
                'trace' => $e->getTraceAsString(),
            ]);

            return [['error' => $e->getMessage()], $payload];
        } catch (\Exception $e) {
            Log::error('General Exception: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [['error' => $e->getMessage()], $payload];
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
