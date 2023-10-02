<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use GuzzleHttp\Promise;
use GuzzleHttp\Client;

class PropertyPriceCall
{
	# https://developers.expediagroup.com/docs/rapid/lodging/shopping#get-/properties/availability

	// Path
	private const PROPERTY_CONTENT_PATH = "v3/properties/availability";

	// Query parameters keys
	private const LANGUAGE = "language";
	private const COUNTRY_CODE = "country_code";
	private const PROPERTY_ID = "property_id";
	private const CHECKIN = "checkin";
	private const CHECKOUT = "checkout";
	private const CURRENCY = "currency";
	private const OCCUPANCY = "occupancy";
	private const RATE_PLAN_COUNT = "rate_plan_count";
	private const SALES_CHANNEL = "sales_channel";
	private const SALES_ENVIRONMENT = "sales_environment";
	private const RATE_OPTION = "rate_option";
	private const BILLING_TERMS = "billing_terms";
	private const PAYMENT_TERMS = "payment_terms";
	private const PARTNER_POINT_SALE = "partner_point_of_sale";


	// Call parameters
	private $client;
	private $propertyId;
	private string $checkin;
	private string $checkout;
	private string $currency;
	private string $countryCode;
	private string $language;

	// 2 adults, one 9-year-old and one 4-year-old would be represented by occupancy=2-9,4.
	// A multi-room request to lodge an additional 2 adults would be represented by occupancy=2-9,4&occupancy=2
	private array $occupancy;

	// 'rate_plan_count' - The number of rates to return per property. 
	// The rates with the best value will be returned, e.g. a rate_plan_count=4 will return the best 4 rates, 
	// but the rates are not ordered from lowest to highest or vice versa in the response. 
	// Generally lowest rates will be prioritized.
	private int $ratePlanCount;
	private string $salesChannel;
	private string $salesEnvironment;

	private $rateOption;
	private $billingTerms;
	private $paymentTerms;
	private $partnerPointSale;

	private $token;

	public function __construct($client, $property)
	{
		$this->client = $client;

		$this->checkin = $property['checkin'];
		$this->checkout = $property['checkout'];

		$this->currency = $property['currency'] ?? "USD";
		$this->countryCode = $property['country_code'] ?? "US";
		$this->language = $property['language'] ?? "en-US";

		$this->occupancy = $property['occupancy'];

		$this->ratePlanCount = $property['rate_plan_count'] ?? 1;
		$this->salesChannel = $property['sales_channel'] ?? "agent_tool";
		$this->salesEnvironment = $property['sales_environment'] ?? "hotel_package";

		$this->rateOption = $property['rate_option'] ?? "member";
		$this->billingTerms = $property['billing_terms'] ?? "";
		$this->paymentTerms = $property['payment_terms'] ?? "BASE_DIR";
		$this->partnerPointSale = $property['partner_point_of_sale'] ?? "B2B_EAC_BASE_DIR";
	}

	public function getPriceData(array $propertyIds = [])
	{
		$responses = [];

		foreach ($propertyIds as $propertyId) {
			$this->propertyId = $propertyId;
			$queryParameters = $this->queryParameters();

			try {
				$promises[$propertyId] = $this->client->getAsync(self::PROPERTY_CONTENT_PATH, $queryParameters);
			} catch (Exception $e) {
				\Log::error('Error while creating promise: ' . $e->getMessage());
			}
		}

		try {
			$responses = Promise\Utils::unwrap($promises);
			$resolvedResponses = Promise\Utils::settle($promises)->wait();

			foreach ($resolvedResponses as $propertyId => $response) {
				if ($response['state'] === 'fulfilled') {
					$data = $response['value']->getBody()->getContents();
					\Log::debug('PropertyPriceCall property_id: ' . $propertyId . ' count ' . count(json_decode($data)));
					$responses[$propertyId] = $data;
				} else {
					\Log::error('Promise for property_id ' . $propertyId . ' failed: ' . $response['reason']->getMessage());
				}
			}
		} catch (Exception $e) {
			\Log::error('Error while processing promises: ' . $e->getMessage());
		}

		return $responses;
	}


	private function queryParameters()
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
			$queryParams[self::OCCUPANCY] = $room;
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
