<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use Exception;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;

class PropertyPriceCall
{
    # https://developers.expediagroup.com/docs/rapid/lodging/shopping#get-/properties/availability

    // Path
    /**
     *
     */
    private const PROPERTY_CONTENT_PATH = "v3/properties/availability";

    // Query parameters keys
    /**
     *
     */
    private const LANGUAGE = "language";
    /**
     *
     */
    private const COUNTRY_CODE = "country_code";
    /**
     *
     */
    private const PROPERTY_ID = "property_id";
    /**
     *
     */
    private const CHECKIN = "checkin";
    /**
     *
     */
    private const CHECKOUT = "checkout";
    /**
     *
     */
    private const CURRENCY = "currency";
    /**
     *
     */
    private const OCCUPANCY = "occupancy";
    /**
     *
     */
    private const RATE_PLAN_COUNT = "rate_plan_count";
    /**
     *
     */
    private const SALES_CHANNEL = "sales_channel";
    /**
     *
     */
    private const SALES_ENVIRONMENT = "sales_environment";
    /**
     *
     */
    private const RATE_OPTION = "rate_option";
    /**
     *
     */
    private const BILLING_TERMS = "billing_terms";
    /**
     *
     */
    private const PAYMENT_TERMS = "payment_terms";
    /**
     *
     */
    private const PARTNER_POINT_SALE = "partner_point_of_sale";


    // Call parameters
    /**
     * @var
     */
    private $client;
    /**
     * @var
     */
    private $propertyId;
    /**
     * @var string|mixed
     */
    private string $checkin;
    /**
     * @var string|mixed
     */
    private string $checkout;
    /**
     * @var string|mixed
     */
    private string $currency;
    /**
     * @var string|mixed
     */
    private string $countryCode;
    /**
     * @var string|mixed
     */
    private string $language;

    // 2 adults, one 9-year-old and one 4-year-old would be represented by occupancy=2-9,4.
    // A multi-room request to lodge an additional 2 adults would be represented by occupancy=2-9,4&occupancy=2
    /**
     * @var array|mixed
     */
    private array $occupancy;

    // 'rate_plan_count' - The number of rates to return per property.
    // The rates with the best value will be returned, e.g. a rate_plan_count=4 will return the best 4 rates,
    // but the rates are not ordered from lowest to highest or vice versa in the response.
    // Generally lowest rates will be prioritized.
    /**
     * @var int|mixed
     */
    private int $ratePlanCount;
    /**
     * @var string|mixed
     */
    private string $salesChannel;
    /**
     * @var string|mixed
     */
    private string $salesEnvironment;

    /**
     * @var mixed|string
     */
    private mixed $rateOption;
    /**
     * @var mixed|string
     */
    private mixed $billingTerms;
    /**
     * @var mixed|string
     */
    private mixed $paymentTerms;
    /**
     * @var mixed|string
     */
    private mixed $partnerPointSale;

    /**
     * @param $client
     * @param $property
     */
    public function __construct($client, $property)
    {
        $this->client = $client;

        $this->checkin = $property['checkin'];
        $this->checkout = $property['checkout'];

        $this->currency = $property['currency'] ?? "USD";
        $this->countryCode = $property['country_code'] ?? "US";
        $this->language = $property['language'] ?? "en-US";

        $this->occupancy = $property['occupancy'];

        // $this->ratePlanCount = $property['rate_plan_count'] ?? 1;
        $this->ratePlanCount = $property['rate_plan_count'] ?? 10;

        // SHOP parameters for Standalone Rates:
        $this->partnerPointSale = $property['partner_point_of_sale'] ?? "B2B_EAC_SA_MOD_DIR";
        $this->billingTerms = $property['billing_terms'] ?? "";
        $this->paymentTerms = $property['payment_terms'] ?? "SA";
        $this->salesChannel = $property['sales_channel'] ?? "agent_tool";
        $this->rateOption = $property['rate_option'] ?? "member";
        $this->salesEnvironment = $property['sales_environment'] ?? "hotel_only";

        // // SHOP parameters for Package Rates:
        // $this->partnerPointSale = $property['partner_point_of_sale'] ?? "B2B_EAC_BASE_DIR";
        // $this->billingTerms = $property['billing_terms'] ?? "";
        // $this->paymentTerms = $property['payment_terms'] ?? "BASE_DIR";
        // $this->salesChannel = $property['sales_channel'] ?? "agent_tool";
        // $this->rateOption = $property['rate_option'] ?? "member";
        // $this->salesEnvironment = $property['sales_environment'] ?? "hotel_package";
    }

    /**
     * @param array $propertyIds
     * @return array
     */
    public function getPriceData(array $propertyIds = []): array
    {
        $responses = [];

        $batchSize = 250;
        $chunkPropertyIds = array_chunk($propertyIds, $batchSize);

        foreach ($chunkPropertyIds as $keyChunk => $chunk) {
            $this->propertyId = $chunk;
            $queryParameters = $this->queryParameters();

            // dd($queryParameters);

            try {
                $promises[$keyChunk] = $this->client->getAsync(self::PROPERTY_CONTENT_PATH, $queryParameters);
            } catch (Exception $e) {
                Log::error('Error while creating promise: ' . $e->getMessage());
            }
        }

        try {
            // $responses = Promise\Utils::unwrap($promises);
            $resolvedResponses = Promise\Utils::settle($promises)->wait();

            foreach ($resolvedResponses as $response) {
                if ($response['state'] === 'fulfilled') {
                    $data = $response['value']->getBody()->getContents();
                    $responses = array_merge($responses, json_decode($data, true));
                } else {
                    Log::error('Promise for property_id ' . $this->propertyId . ' failed: ' . $response['reason']->getMessage());
                }
            }
        } catch (Exception $e) {
            Log::error('Error while processing promises: ' . $e->getMessage());
        }

        $res = [];
        foreach ($responses as $response) {
            $res[$response['property_id']] = $response;
        }

        return $res;
    }


    /**
     * @return array
     */
    private function queryParameters(): array
    {
        $queryParams = [];

        // Add required parameters
        $queryParams[self::PROPERTY_ID] = $this->propertyId;
        $queryParams[self::LANGUAGE] = $this->language;
        $queryParams[self::COUNTRY_CODE] = $this->countryCode;

        $queryParams[self::CHECKIN] = $this->checkin;
        $queryParams[self::CHECKOUT] = $this->checkout;

        $queryParams[self::CURRENCY] = $this->currency;

        foreach ($this->occupancy as $room) {
            if (isset($room['children_ages'])) {
                $queryParams[self::OCCUPANCY][] = $room['adults'] . '-' . implode(',', $room['children_ages']);
            } else {
                $queryParams[self::OCCUPANCY][] = $room['adults'];
            }
        }
        $queryParams[self::RATE_PLAN_COUNT] = $this->ratePlanCount;
        $queryParams[self::SALES_CHANNEL] = $this->salesChannel;
        $queryParams[self::SALES_ENVIRONMENT] = $this->salesEnvironment;

        // Add optional parameters
        if (!empty($this->rateOption)) {
            $queryParams[self::RATE_OPTION] = $this->rateOption;
        }
        if (!empty($this->billingTerms)) {
            $queryParams[self::BILLING_TERMS] = $this->billingTerms;
        }
        if (!empty($this->paymentTerms)) {
            $queryParams[self::PAYMENT_TERMS] = $this->paymentTerms;
        }
        if (!empty($this->partnerPointSale)) {
            $queryParams[self::PARTNER_POINT_SALE] = $this->partnerPointSale;
        }

        return $queryParams;
    }
}
