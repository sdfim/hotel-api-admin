<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use App\Jobs\SaveSearchInspector;
use Exception;
use Fiber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PropertyPriceCall
{
    public const STANDALONE_RATES = [
        'partner_point_of_sale' => 'B2B_EAC_SA_MOD_DIR',
        'billing_terms' => '',
        'payment_terms' => 'SA',
        'sales_channel' => 'agent_tool',
        'rate_option' => 'member',
        'sales_environment' => 'hotel_only',
    ];

    public const PACKAGE_RATES = [
        'partner_point_of_sale' => 'B2B_EAC_BASE_DIR',
        'billing_terms' => '',
        'payment_terms' => 'BASE_DIR',
        'sales_channel' => 'agent_tool',
        'rate_option' => 'member',
        'sales_environment' => 'hotel_package',
    ];

    private const RATE_PLAN_COUNT = 250;

    // https://developers.expediagroup.com/docs/rapid/lodging/shopping#get-/properties/availability

    // Path
    private const PROPERTY_CONTENT_PATH = 'v3/properties/availability';

    // Query parameters keys

    private const LANGUAGE = 'language';

    private const COUNTRY_CODE = 'country_code';

    private const PROPERTY_ID = 'property_id';

    private const CHECKIN = 'checkin';

    private const CHECKOUT = 'checkout';

    private const CURRENCY = 'currency';

    private const OCCUPANCY = 'occupancy';

    private const KEY_RATE_PLAN_COUNT = 'rate_plan_count';

    private const SALES_CHANNEL = 'sales_channel';

    private const SALES_ENVIRONMENT = 'sales_environment';

    private const RATE_OPTION = 'rate_option';

    private const BILLING_TERMS = 'billing_terms';

    private const PAYMENT_TERMS = 'payment_terms';

    private const PARTNER_POINT_SALE = 'partner_point_of_sale';

    private const BATCH_SIZE = 250;

    // Call parameters
    private ?RapidClient $client;

    private array $propertyChunk;

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

    public function __construct($client, $property)
    {
        $this->client = $client;

        $this->checkin = $property['checkin'];
        $this->checkout = $property['checkout'];

        $this->currency = $property['currency'] ?? 'USD';
        $this->countryCode = $property['country_code'] ?? 'US';
        $this->language = $property['language'] ?? 'en-US';

        $this->occupancy = $property['occupancy'];

        $this->ratePlanCount = $property['rate_plan_count'] ?? self::RATE_PLAN_COUNT;

        $rateType = env('SUPPLIER_EXPEDIA_RATE_TYPE', 'standalone');

        Log::info('Rate Type: '.$rateType);

        $rates = $rateType === 'package' ? self::PACKAGE_RATES : self::STANDALONE_RATES;

        $this->partnerPointSale = $rates['partner_point_of_sale'];
        $this->billingTerms = $rates['billing_terms'];
        $this->paymentTerms = $rates['payment_terms'];
        $this->salesChannel = $rates['sales_channel'];
        $this->rateOption = $rates['rate_option'];
        $this->salesEnvironment = $rates['sales_environment'];
    }

    /**
     * @throws Throwable
     */
    public function getPriceData(array $propertyIds, array $searchInspector): array
    {
        $chunkPropertyIds = array_chunk($propertyIds, self::BATCH_SIZE);
        $rq = [];

        foreach ($chunkPropertyIds as $keyChunk => $chunk) {
            $this->propertyChunk = $chunk;
            $queryParameters = $this->queryParameters();

            $rq[$keyChunk]['params'] = $queryParameters;
            $rq[$keyChunk]['path'] = self::PROPERTY_CONTENT_PATH;

            try {
                $promises[$keyChunk] = $this->client->getAsync(self::PROPERTY_CONTENT_PATH, $queryParameters);
            } catch (Exception $e) {
                Log::error('Error while creating promise: '.$e->getMessage());
                Log::error($e->getTraceAsString());
            }
        }

        $responses = Fiber::suspend($promises);

        try {
            foreach ($responses as $response) {
                if ($response['state'] === 'fulfilled') {
                    $data = $response['value']->getBody()->getContents();
                    $responses = array_merge($responses, json_decode($data, true));
                } elseif (! isset($response['value'])) {
                    Log::error('Expedia Timeout Exception after '.$duration.' seconds');
                    $parent_search_id = $searchInspector['search_id'];
                    $searchInspector['search_id'] = Str::uuid();
                    SaveSearchInspector::dispatch($searchInspector, [], [], [], 'error',
                        ['side' => 'supplier', 'message' => 'Expedia Timeout Exception  ', 'parent_search_id' => $parent_search_id]);

                    return ['error' => 'Timeout Exception '];
                } else {
                    Log::error('PropertyPriceCall | getPriceData ', [
                        'propertyChunk' => $this->propertyChunk,
                        'reason' => $response['reason']->getMessage(),
                    ]);
                }
            }

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            // Timeout
            Log::error('Connection timeout: '.$e->getMessage());
            Log::error($e->getTraceAsString());
            $parent_search_id = $searchInspector['search_id'];
            $searchInspector['search_id'] = Str::uuid();
            SaveSearchInspector::dispatch($searchInspector, [], [], [], 'error',
                ['side' => 'supplier', 'message' => 'Expedia Connection timeout', 'parent_search_id' => $parent_search_id]);

            return ['error' => 'Connection timeout'];
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Error 500
            Log::error('Server error: '.$e->getMessage());
            Log::error($e->getTraceAsString());
            $parent_search_id = $searchInspector['search_id'];
            $searchInspector['search_id'] = Str::uuid();
            SaveSearchInspector::dispatch($searchInspector, [], [], [], 'error',
                ['side' => 'supplier', 'message' => 'Expedia Server error', 'parent_search_id' => $parent_search_id]);

            return ['error' => 'Server error'];
        } catch (Exception $e) {
            Log::error('Error while processing promises: '.$e->getMessage());
            Log::error($e->getTraceAsString());
        }

        $res = [];
        if (! empty($responses)) {
            foreach ($responses as $response) {
                if (isset($response['property_id'])) {
                    $res[$response['property_id']] = $response;
                }
            }
        }

        return [
            'request' => $rq,
            'response' => $res,
        ];
    }

    private function queryParameters(): array
    {
        $queryParams = [];

        // Add required parameters
        $queryParams[self::PROPERTY_ID] = $this->propertyChunk;
        $queryParams[self::LANGUAGE] = $this->language;
        $queryParams[self::COUNTRY_CODE] = $this->countryCode;

        $queryParams[self::CHECKIN] = $this->checkin;
        $queryParams[self::CHECKOUT] = $this->checkout;

        $queryParams[self::CURRENCY] = $this->currency === '*' ? 'USD' : $this->currency;

        foreach ($this->occupancy as $room) {
            if (isset($room['children_ages'])) {
                $queryParams[self::OCCUPANCY][] = $room['adults'].'-'.implode(',', $room['children_ages']);
            } else {
                $queryParams[self::OCCUPANCY][] = $room['adults'];
            }
        }
        $queryParams[self::KEY_RATE_PLAN_COUNT] = $this->ratePlanCount;
        $queryParams[self::SALES_CHANNEL] = $this->salesChannel;
        $queryParams[self::SALES_ENVIRONMENT] = $this->salesEnvironment;

        // Add optional parameters
        if (! empty($this->rateOption)) {
            $queryParams[self::RATE_OPTION] = $this->rateOption;
        }
        if (! empty($this->billingTerms)) {
            $queryParams[self::BILLING_TERMS] = $this->billingTerms;
        }
        if (! empty($this->paymentTerms)) {
            $queryParams[self::PAYMENT_TERMS] = $this->paymentTerms;
        }
        if (! empty($this->partnerPointSale)) {
            $queryParams[self::PARTNER_POINT_SALE] = $this->partnerPointSale;
        }

        return $queryParams;
    }
}
