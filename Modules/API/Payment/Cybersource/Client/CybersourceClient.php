<?php

namespace Modules\API\Payment\Cybersource\Client;

use CyberSource\Api\MicroformIntegrationApi;
use CyberSource\Api\PaymentsApi;
use CyberSource\ApiClient;
use CyberSource\ApiException;
use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Configuration;
use CyberSource\Model\CreatePaymentRequest;
use CyberSource\Model\GenerateCaptureContextRequest;
use CyberSource\Model\Ptsv2paymentsClientReferenceInformation;
use CyberSource\Model\Ptsv2paymentsOrderInformation;
use CyberSource\Model\Ptsv2paymentsOrderInformationAmountDetails;
use CyberSource\Model\Ptsv2paymentsOrderInformationBillTo;
use CyberSource\Model\Ptsv2paymentsProcessingInformation;
use CyberSource\Model\Ptsv2paymentsTokenInformation;
use CyberSource\ObjectSerializer;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class CybersourceClient
{
    /**
     * MicroformIntegrationApi instance used to create capture context.
     */
    private MicroformIntegrationApi $microformApi;

    /**
     * Shared ApiClient instance for all Cybersource APIs.
     */
    private ApiClient $apiClient;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        // Configure merchant authentication.
        $merchantConfig = new MerchantConfiguration();
        $merchantConfig->setAuthenticationType('HTTP_SIGNATURE');
        $merchantConfig->setMerchantID(config('cybersource.merchant_id'));
        $merchantConfig->setApiKeyID(config('cybersource.api_key_id'));
        $merchantConfig->setSecretKey(config('cybersource.api_secret_key'));
        $merchantConfig->setRunEnvironment(config('cybersource.environment'));

        // Build SDK configuration with correct host.
        $config = new Configuration();
        $config->setHost($merchantConfig->getHost());

        // ApiClient requires both Configuration and MerchantConfiguration.
        $this->apiClient    = new ApiClient($config, $merchantConfig);
        $this->microformApi = new MicroformIntegrationApi($this->apiClient);
    }

    /**
     * Generates capture context (JWT) for Microform initialization.
     *
     * @param string $origin Frontend origin where Microform is rendered.
     * @return array JWT capture context.
     * @throws ApiException
     */
    public function generateCaptureContext(string $origin): array
    {
        // Minimal set according to docs:
        // clientVersion, targetOrigins, allowedCardNetworks.
        $payload = [
            'clientVersion' => config('cybersource.client_version'),
            'targetOrigins' => [$origin],
            'allowedCardNetworks' => config('cybersource.allowed_card_networks'),
            'allowedPaymentTypes' => config('cybersource.allowed_payment_types'),
        ];

        $request = new GenerateCaptureContextRequest($payload);

        $response = $this->microformApi->generateCaptureContext($request);

        // SDK may return:
        //  - [jwt, statusCode, headers]
        //  - ['captureContext' => jwt, ...]
        //  - plain string jwt
        //  - object with getCaptureContext()
        $jwt = '';
        if (is_array($response)) {
            if (isset($response[0]) && is_string($response[0])) {
                $jwt = $response[0];
            } elseif (isset($response['captureContext']) && is_string($response['captureContext'])) {
                $jwt = $response['captureContext'];
            } else {
                return [['error' => 'Unexpected array response from Cybersource generateCaptureContext().'], $payload];
            }
        } elseif (is_string($response)) {
            $jwt = $response;
        } elseif (is_object($response) && method_exists($response, 'getCaptureContext')) {
            /** @var mixed $ctx */
            $ctx = $response->getCaptureContext();
            if (!is_string($ctx)) {
                return [['error' => 'Unexpected captureContext type on response object.'], $payload];
            }
            $jwt = $ctx;
        } else {
            return [['error' => 'Unexpected response type from Cybersource generateCaptureContext(): ' . gettype($response)], $payload];
        }

        if ($jwt === '') {
            return [['error' => 'Empty capture context returned from Cybersource.'], $payload];
        }

        return [['captureContext' => $jwt], $payload];
    }

    /**
     * Fetches RSA public key (JWK) for the given key ID (kid) using signed request.
     * Cybersource JWK does NOT include "alg", so we add it manually for Firebase\JWT.
     *
     * @param string $kid
     * @return array|null
     */
    public function fetchPublicKeyForKid(string $kid): ?array
    {
        $resourcePath = '/flex/v2/public-keys/' . urlencode($kid);

        try {
            /**
             * callApi handles:
             * - attaching the correct base host (api[|test].cybersource.com)
             * - HTTP_SIGNATURE auth
             * - Digest header
             */
            $response = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                [],  // query params
                [],  // path params
                ['Accept' => 'application/json'] // body is null for GET
            );

            /**
             * SDK usually returns:
             *  [0 => stdClass(data), 1 => statusCode, 2 => headers]
             */
            if (!isset($response[0])) {
                \Log::warning('Cybersource JWK: response[0] missing', ['kid' => $kid]);
                return null;
            }

            // Convert to associative array
            $jwk = json_decode(json_encode($response[0]), true);

            if (!is_array($jwk)) {
                \Log::warning('Cybersource JWK: invalid json structure', ['kid' => $kid, 'data' => $response[0]]);
                return null;
            }

            // ----------- FIX: Cybersource JWK does not include "alg" -----------
            // Firebase\JWT requires alg, so we set it explicitly based on spec:
            if (!isset($jwk['alg'])) {
                $jwk['alg'] = 'RS256';
            }

            return $jwk;
        } catch (Throwable $e) {
            \Log::error('Cybersource JWK fetch failed', [
                'kid'       => $kid,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a payment (authorize/capture) using a transient token JWT.
     *
     * This calls POST /pts/v2/payments via the official PHP SDK.
     *
     * @param string $transientTokenJwt
     * @param float  $amount
     * @param string $currency
     * @param array  $billTo  firstName, lastName, email, address1, locality,
     *                        administrativeArea, postalCode, country, reference
     *
     * @return array Decoded payment response as associative array.
     * @throws Exception
     */
    public function createPaymentWithTransientToken(
        string $transientTokenJwt,
        float $amount,
        string $currency,
        array $billTo
    ): array {
        // clientReferenceInformation
        $clientRef = new Ptsv2paymentsClientReferenceInformation([
            'code' => $billTo['reference'] ?? null,
        ]);

        // orderInformation.amountDetails
        $amountDetails = new Ptsv2paymentsOrderInformationAmountDetails([
            'totalAmount' => number_format($amount, 2, '.', ''),
            'currency'    => $currency,
        ]);

        // orderInformation.billTo
        $billToPayload = [
            'firstName'  => $billTo['firstName'] ?? 'Guest',
            'lastName'   => $billTo['lastName'] ?? 'Customer',
            'email'      => $billTo['email'] ?? 'no-reply@example.com',
            'address1'   => $billTo['address1'] ?? 'N/A',
            'locality'   => $billTo['locality'] ?? 'N/A',
            'postalCode' => $billTo['postalCode'] ?? '00000',
            'country'    => $billTo['country'] ?? 'US',
            'administrativeArea' => $billTo['administrativeArea'] ?? 'CA'
        ];

        $billToModel = new Ptsv2paymentsOrderInformationBillTo($billToPayload);

        $orderInfo = new Ptsv2paymentsOrderInformation([
            'amountDetails' => $amountDetails,
            'billTo'        => $billToModel,
        ]);

        // tokenInformation.transientTokenJwt
        $tokenInfo = new Ptsv2paymentsTokenInformation([
            'transientTokenJwt' => $transientTokenJwt,
        ]);

        $processingInformation = new Ptsv2paymentsProcessingInformation([
            'capture' => true, // if you want auth+capture right away
        ]);

        $request = new CreatePaymentRequest([
            'clientReferenceInformation' => $clientRef,
            'orderInformation'           => $orderInfo,
            'tokenInformation'           => $tokenInfo,
            'processingInformation'      => $processingInformation,
        ]);

        $paymentsApi = new PaymentsApi($this->apiClient);

        $payload = json_decode(
            json_encode(ObjectSerializer::sanitizeForSerialization($request), JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        try {
            // IMPORTANT: use WithHttpInfo to get status code + headers
            [$result, $statusCode, $headers] = $paymentsApi->createPaymentWithHttpInfo($request);

            Log::info('Cybersource createPayment meta', [
                'status_code' => $statusCode,
                'request_id'  => $headers['X-RequestID'][0] ?? null,
                'corr_id'     => $headers['v-c-correlation-id'][0] ?? null,
            ]);

            // IMPORTANT: SDK models often can’t be json_encoded directly -> use ObjectSerializer
            $sanitized = ObjectSerializer::sanitizeForSerialization($result);

            /** @var array $decoded */
            $decoded = json_decode(
                json_encode($sanitized, JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            // If Cybersource returns non-2xx but SDK didn't throw, still treat as error
            if ($statusCode < 200 || $statusCode >= 300) {
                Log::warning('Cybersource createPayment non-2xx', [
                    'status_code' => $statusCode,
                    'decoded'     => $decoded,
                ]);

                return [['error' => 'Cybersource createPayment failed with HTTP ' . $statusCode], $payload];
            }

            return [$decoded, $payload];
        } catch (ApiException $e) {
            Log::error('Cybersource ApiException (createPayment)', [
                'code'          => $e->getCode(),
                'message'       => $e->getMessage(),
                'response_body' => $e->getResponseBody(),
                'headers'       => $e->getResponseHeaders(),
            ]);

            return [['error' => $e->getMessage()], $payload];
        }
    }
}
