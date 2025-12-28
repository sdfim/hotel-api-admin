<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Resolvers;

use Modules\API\PricingAPI\ResponseModels\TaxFee\RateItemTaxFee;
use Modules\API\PricingAPI\ResponseModels\TaxFee\TransformedRate;

interface HotelTaxAndFeeResolverInterface
{
    /**
     * Transform rates from the Supplier response.
     * Converts Supplier data into an Transformed nightly rates (General structure) -
     * array of nightly rates compatible with
     * the logic of applying taxes/fees, and extracts stay-level charges.
     *
     * @param  array  $rates  Rate data from the Supplier response (expected to be an array of room rate details).
     * @param  array  $repoTaxFees  Taxes and fees from the repository (not directly used here, but kept for signature).
     * @param  string  $checkin  The check-in date.
     * @return array<array{
     *      code: string,
     *      effective_date: string,
     *      expire_date: string,
     *      amount_before_tax: float,
     *      amount_after_tax: float,
     *      currency_code: string,
     *      taxes: array<RateItemTaxFee>,
     *      fees: array<RateItemTaxFee>,
     *  }>|array<TransformedRate> Transformed nightly rates. General structure provided.
     */
    public function transformRates(array $rates, array $repoTaxFees, string $checkin = '', string $checkout = ''): array;
}
