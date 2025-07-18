<?php

namespace Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Enums\ProductApplyTypeEnum;

/**
 * @todo Check how to handle a commissionable rate that has no rack amount
 */
class TaxAndFeeResolver
{
    /**
     * Transform rates from the response.
     *
     * @param array $rates The rates to transform.
     * @return array The transformed rates.
     */
    public function transformRates(array $rates, array $repoTaxFees): array
    {
        $transformedRates = [];

        if (isset($rates['Rate']['@attributes'])) {
            $rate = $rates['Rate'];
            $transformedRates[] = $this->transformRate($rate);
        } else {
            foreach ($rates['Rate'] as $rate) {
                $transformedRates[] = $this->transformRate($rate);
            }
        }

        $numberOfNights = array_sum(array_column($transformedRates, 'UnitMultiplier'));

        foreach ($transformedRates as &$rate) {
            $rate['Taxes'] = $rate['Taxes'] ?? [];

            if (!empty($repoTaxFees['vat'])) {
                $vat = array_values($repoTaxFees['vat'])[0];
                $vatPercentage = $vat['net_value'] ?? 0;

                // Calculate VAT
                $vatAmount = ($rate['TotalAmountBeforeTax'] / (1 + $vatPercentage / 100)) * ($vatPercentage / 100);
                $rate['TotalAmountBeforeTax'] -= $vatAmount;

                $vatRate = round($vatAmount / $numberOfNights, 2);

                // Add VAT to Taxes
                $rate['Taxes'][] = [
                    'Type' => 'Inclusive',
                    'Amount' => $vatRate,
                    'Description' => 'VAT',
                ];

                $rate['AmountBeforeTax'] = round($rate['AmountBeforeTax'] - $vatRate, 2);
            }
        }

        return $transformedRates;
    }

    /**
     * Transform a single rate from the response.
     *
     * @param array $rate The rate to transform.
     * @return array The transformed rate.
     */
    private function transformRate(array $rate): array
    {
        $transformedRate = [
            'Code' => $rate['@attributes']['Code'] ?? '',
            'RateTimeUnit' => $rate['@attributes']['RateTimeUnit'] ?? '',
            'UnitMultiplier' => $rate['@attributes']['UnitMultiplier'] ?? '',
            'EffectiveDate' => $rate['@attributes']['EffectiveDate'] ?? '',
            'ExpireDate' => $rate['@attributes']['ExpireDate'] ?? '',
            'AmountBeforeTax' => $rate['Base']['@attributes']['AmountBeforeTax'] ?? '',
            'AmountAfterTax' => $rate['Base']['@attributes']['AmountAfterTax'] ?? '',
            'CurrencyCode' => $rate['Base']['@attributes']['CurrencyCode'] ?? '',
            'Taxes' => $this->transformTaxes($rate['Base']['Taxes']['Tax'] ?? []),
            'TotalAmountBeforeTax' => $rate['Total']['@attributes']['AmountBeforeTax'] ?? '',
            'TotalAmountAfterTax' => $rate['Total']['@attributes']['AmountAfterTax'] ?? '',
            'TotalCurrencyCode' => $rate['Total']['@attributes']['CurrencyCode'] ?? '',
        ];

        return $transformedRate;
    }

    /**
     * Transform taxes from the response.
     *
     * @param array $taxes The taxes to transform.
     * @return array The transformed taxes.
     */
    private function transformTaxes(array $taxes): array
    {
        $transformedTaxes = [];
        // it means that is not an array
        if (isset($taxes['@attributes'])) {
            $taxes = [$taxes];
        }

        foreach ($taxes as $tax) {
            $transformedTaxes[] = [
                'Type' => $tax['@attributes']['Type'] ?? '',
                'Code' => $tax['@attributes']['Code'] ?? '',
                'Level' => 'rate',
                'CollectedBy' => 'vendor',
                'Amount' => $tax['@attributes']['Amount'] ?? '',
                'Description' => $tax['TaxDescription']['Text'] ?? '',
            ];
        }

        return $transformedTaxes;
    }

    /**
     * Apply repo tax fees to the transformed rates.
     *
     * @param array $transformedRates The transformed rates.
     * @param string $giataCode The GIATA code.
     * @param string $ratePlanCode The rate plan code.
     * @param string $unifiedRoomCode The unified room code.
     * @param int $numberOfPassengers The number of passengers.
     * @param string $checkinInput The check-in date.
     * @param string $checkoutInput The check-out date.
     * @param array $repoTaxFeesInput The repo tax fees input.
     */
    public function applyRepoTaxFees(array &$transformedRates, $giataCode, $ratePlanCode, $unifiedRoomCode, $numberOfPassengers, $checkinInput, $checkoutInput, $repoTaxFeesInput, $occupancy): void
    {
        $repoTaxFees = Arr::get($repoTaxFeesInput, $giataCode, []);
        $numberOfNights = array_sum(array_column($transformedRates, 'UnitMultiplier'));

        $checkin = Carbon::parse($checkinInput);
        $checkout = Carbon::parse($checkoutInput);

        // Filter repoTaxFees based on start_date and end_date
        foreach ($repoTaxFees as $key => $fees) {
            $repoTaxFees[$key] = array_filter($fees, function ($feeTax) use ($checkin, $checkout) {
                $startDate = Arr::get($feeTax, 'start_date', '');
                $endDate = Arr::get($feeTax, 'end_date', '');

                // If start_date and end_date are not set, apply the rule
                if (empty($startDate) || empty($endDate)) {
                    return true;
                }

                $startDate = Carbon::parse($startDate);
                $endDate = Carbon::parse($endDate);

                // Check if the interval $this->checkin - $this->checkout falls within start_date - end_date
                return $checkin->between($startDate, $endDate) && $checkout->between($startDate, $endDate);
            });
        }

        // Filter repoTaxFees based on children's ages in occupancy
        foreach ($repoTaxFees as $key => $fees) {
            $repoTaxFees[$key] = array_filter($fees, function ($feeTax) use ($occupancy) {
                $ageFrom = (int) Arr::get($feeTax, 'age_from', 0);
                $ageTo = (int) Arr::get($feeTax, 'age_to', 99);

                // If no age limits, always include
                if ($ageFrom === 0 && ($ageTo === 99 || $ageTo === 0)) {
                    return true;
                }

                foreach ($occupancy as $occ) {
                    foreach ($occ['children_ages'] ?? [] as $childAge) {
                        if ($childAge >= $ageFrom && $childAge <= $ageTo) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        foreach ($transformedRates as &$rate) {

            $this->initializeFeesArray($rate);

            if (isset($repoTaxFeesInput[$giataCode]) && !empty($repoTaxFeesInput[$giataCode])) {

                // Apply edits
                if (isset($repoTaxFees['edit'])) {
                    foreach ($repoTaxFees['edit'] as $editFeeTax) {

                        if ($this->isRateExcluded($editFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                            continue;
                        }

                        foreach ($rate['Taxes'] ?? [] as $key => &$tax) {

                            if (!is_int($key) && !is_string($key)) {
                                return;
                            }

                            if (!$this->isDescriptionMatching($tax['Description'], $editFeeTax['old_name'])) {
                                return;
                            }

                            $rateData = $this->getRateData($editFeeTax, $numberOfNights, $numberOfPassengers);
                            $baseRate = $rate['AmountBeforeTax'] ?? 0;

                            if ($editFeeTax['type'] === 'Fee') {
                                $feeData = $this->getFeeData($editFeeTax, $numberOfPassengers);
                                $rateData = array_merge($rateData, $feeData);
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $editFeeTax,
                                    $numberOfNights,
                                    $numberOfPassengers,
                                    'Fee',
                                );
                                $rateData = array_merge($rateData, $amountData);
                                $rate['Fees'][] = $rateData;
                                unset($rate['Taxes'][$key]);
                            } else {
                                $rateData = array_merge($rateData, $this->getTaxData($editFeeTax));
                                $rateData['MultiplierFee'] = $rate['UnitMultiplier'];
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $editFeeTax,
                                    $numberOfNights,
                                    $numberOfPassengers,
                                    'Tax',
                                );
                                $rateData = array_merge($rateData, $amountData);
                                $rate['Taxes'][] = $rateData;
                                unset($rate['Taxes'][$key]);
                            }
                        }

                        foreach ($rate['Fees'] ?? [] as $key => &$fee) {
                            if (!is_int($key) && !is_string($key)) {
                                return;
                            }

                            if (!$this->isDescriptionMatching($fee['Description'], $editFeeTax['old_name'])) {
                                return;
                            }

                            $feeData = $this->getRateData($editFeeTax, $numberOfNights, $numberOfPassengers);
                            $amountData = $this->getRateAmountData(
                                $baseRate,
                                $editFeeTax,
                                $numberOfNights,
                                $numberOfPassengers,
                                'Fee',
                            );
                            $feeData = array_merge($feeData, $amountData);
                            $rate['Fees'][] = $feeData;
                            unset($rate['Fees'][$key]);
                        }
                    }
                }

                // Apply updates
                if (isset($repoTaxFees['update'])) {
                    foreach ($repoTaxFees['update'] as $updateFeeTax) {

                        if ($this->isRateExcluded($updateFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                            continue;
                        }

                        foreach ($rate['Taxes'] as $key => $tax) {

                            if (!is_int($key) && !is_string($key)) {
                                continue;
                            }

                            if (!$this->isDescriptionMatching($tax['Description'], $updateFeeTax['old_name'])) {
                                continue;
                            }

                            $rateData = $this->getRateData($updateFeeTax, $numberOfNights, $numberOfPassengers);
                            $baseRate = $rate['AmountBeforeTax'] ?? 0;

                            foreach (['Amount', 'DisplayableAmount', 'RackAmount', 'DisplayableRackAmount'] as $field) {
                                if (isset($tax[$field])) {
                                    $rateData[$field] = $tax[$field];
                                }
                            }

                            if ($updateFeeTax['type'] === 'Fee') {
                                $feeData = $this->getFeeData($updateFeeTax, $numberOfPassengers);
                                $rateData = array_merge($rateData, $feeData);
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $updateFeeTax,
                                    $numberOfNights,
                                    $numberOfPassengers,
                                    'Fee',
                                );
                                foreach (['Amount', 'DisplayableAmount', 'RackAmount', 'DisplayableRackAmount'] as $field) {
                                    if (!isset($rateData[$field]) && isset($amountData[$field])) {
                                        $rateData[$field] = $amountData[$field];
                                    }
                                }
                                $rate['Fees'][] = $rateData;
                            } else {
                                $rateData = array_merge($rateData, $this->getTaxData($updateFeeTax));
                                $rateData['Type'] = 'Inclusive';
                                $rateData['MultiplierFee'] = $rate['UnitMultiplier'];
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $updateFeeTax,
                                    $numberOfNights,
                                    $numberOfPassengers,
                                    'Tax',
                                );
                                $rateData = array_merge($rateData, $amountData);
                                $rate['Taxes'][] = $rateData;
                            }
                            unset($rate['Taxes'][$key]);
                        }
                        $rate['Taxes'] = array_values($rate['Taxes']);
                    }
                }

                // Apply additions
                if (isset($repoTaxFees['add'])) {
                    foreach ($repoTaxFees['add'] as $addFeeTax) {

                        if ($this->isRateExcluded($addFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                            continue;
                        }

                        if ($addFeeTax['fee_category'] !== 'mandatory') {
                            continue;
                        }

                        $rateData = $this->getRateData($addFeeTax, $numberOfNights, $numberOfPassengers);
                        $baseRate = $rate['AmountBeforeTax'] ?? 0;

                        if ($addFeeTax['type'] === 'Fee') {
                            $feeData = $this->getFeeData($addFeeTax, $numberOfPassengers);
                            $rateData = array_merge($rateData, $feeData);
                            $amountData = $this->getRateAmountData(
                                $baseRate,
                                $addFeeTax,
                                $numberOfNights,
                                $numberOfPassengers,
                                'Fee',
                            );
                            $rateData = array_merge($rateData, $amountData);
                            $rate['Fees'][] = $rateData;
                        } else {
                            $rateData = array_merge($rateData, $this->getTaxData($addFeeTax));
                            $rateData['MultiplierFee'] = $rate['UnitMultiplier'];
                            $amountData = $this->getRateAmountData(
                                $baseRate,
                                $addFeeTax,
                                $numberOfNights,
                                $numberOfPassengers,
                                'Tax',
                            );
                            $rateData = array_merge($rateData, $amountData);
                            $rate['Taxes'][] = $rateData;
                        }
                    }
                }

                // Apply deletions
                if (isset($repoTaxFees['delete'])) {

                    foreach ($repoTaxFees['delete'] as $deleteFeeTax) {

                        if ($this->isRateExcluded($deleteFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                            continue;
                        }

                        $rate['Taxes'] = array_filter($rate['Taxes'], function ($tax) use ($deleteFeeTax) {
                            return !$this->isDescriptionMatching($tax['Description'], $deleteFeeTax['old_name']);
                        });
                    }
                }
            }

            foreach ($rate['Taxes'] ?? [] as $key => &$tax) {
                if (Arr::get($tax, 'Type') === 'PropertyCollects') {
                    if (! isset($rate['Fees'])) {
                        $rate['Fees'] = [];
                    }
                    $tax['collected_by'] = 'direct';
                }
            }
        }
    }

    /**
     * Get the transformed breakdown of rates.
     *
     * @param array $transformedRates The transformed rates.
     * @param array $inputFees The input fees.
     * @return array The transformed breakdown of rates.
     */
    public function getTransformedBreakdown(array $transformedRates, array $inputFees): array
    {
        $breakdown = [];
        $night = 0;
        $stay = [];
        $fees = [];

        foreach ($transformedRates as $rate) {
            // Adjust the base amount by removing inclusive taxes if applicable
            $totalInclusiveTax = 0;
            foreach ($rate['Taxes'] as $tax) {
                if ($tax['Type'] === 'Inclusive') {
                    $totalInclusiveTax += (float) $tax['Amount'];
                }
            }

            if ($rate['AmountBeforeTax'] == $rate['AmountAfterTax'] && $totalInclusiveTax > 0) {
                $rate['AmountBeforeTax'] -= $totalInclusiveTax;
            }

            $fareRate = [
                'amount' => $rate['AmountBeforeTax'],
                'rack_amount' => 0,
                'title' => 'Base Rate',
                'level' => 'rate',
                'collected_by' => 'vendor',
                'type' => 'base_rate',
            ];

            $baseFareRate = array_merge($fareRate, [
                'amount' => $rate['AmountBeforeTax'],
            ]);

            $nightsRate = $rate['UnitMultiplier'];
            $baseFareRateNight = array_merge($fareRate, [
                'amount' => round($rate['AmountBeforeTax'], 2),
            ]);

            $taxesRate = [];
            $feesRate = [];

            foreach ($rate['Taxes'] as $tax) {
                $code = strtolower($tax['Code'] ?? '');
                $type = in_array($code, $inputFees) ? 'fee' : 'tax';

                $taxData = [
                    'type' => $type,
                    'amount' => $tax['DisplayableAmount'] ?? (float) $tax['Amount'] ?? 0,
                    'level' => $tax['Level'] ?? null,
                    'rack_amount' => $tax['DisplayableRackAmount'] ?? (float) $tax['Amount'] ?? 0,
                    'title' => $tax['Description'] ?? ($tax['Amount'].' '.$tax['Code']),
                    'is_commissionable' => $tax['IsCommissionable'] ?? false,
                ];

                if (isset($tax['CollectedBy'])) {
                    $taxData['collected_by'] = $tax['CollectedBy'];
                }

                $taxesRate[] = $taxData;
            }

            foreach (Arr::get($rate, 'Fees', []) as $fee) {
                $multiplierFee = Arr::get($fee, 'MultiplierFee', 1);
                $code = strtolower($fee['Code']);
                $type = 'fee';

                if (($fee['ObeAction'] ?? null) === 'update') {
                    $amountValue = isset($fee['DisplayableAmount']) ? $fee['DisplayableAmount'] : ($fee['Amount'] ?? 0);
                } else {
                    $amountValue = $fee['DisplayableAmount'] ?? null;
                }

                $feeData = [
                    'type' => $type,
                    'amount' => $amountValue * $multiplierFee,
                    'rack_amount' => ($fee['DisplayableRackAmount'] ?? null),
                    'level' => $tax['Level'] ?? null,
                    'title' => $fee['Description'] ?? ($fee['Amount'].' '.$fee['Code']),
                    'multiplier' => $multiplierFee,
                    'is_commissionable' => $fee['IsCommissionable'] ?? false,
                ];

                if (isset($fee['CollectedBy'])) {
                    $feeData['collected_by'] = $fee['CollectedBy'];
                }

                $feesRate[] = $feeData;
            }

            for ($i = 0; $i < $nightsRate; $i++) {
                $breakdown[$night][] = $baseFareRateNight;
                $breakdown[$night] = array_merge($breakdown[$night], $taxesRate);
                $night++;
            }

            $stay[] = $baseFareRate;
            $fees = array_values(array_unique(array_merge($fees, $feesRate), SORT_REGULAR));
        }

        return [
            'nightly' => $breakdown,
            // TODO: check if this is correct
            //            'stay' => $stay,
            'stay' => [],
            'fees' => $fees,
        ];
    }

    /**
     * Calculate the rate amount based on the apply type.
     *
     * @param bool $isRounded Whether to round the value.
     * @param float $value The value to be calculated.
     * @param string $applyType The apply type (e.g., PER_PERSON, PER_NIGHT_PER_PERSON).
     * @param string $valueType The value type (e.g., Percentage, Fixed).
     * @param int $numberOfNights The number of nights.
     * @param int|null $numberOfPassengers The number of passengers (optional).
     * @return float The calculated rate amount.
     */
    private function calculateTaxAmount(
        bool $isRounded,
        float $baseRate,
        float $value,
        string $applyType,
        string $valueType,
        int $numberOfNights,
        ?int $numberOfPassengers = null
    ): float
    {
        if ($valueType === 'Percentage') {
            $baseRateBeforeTax = $baseRate / (1 + ($value / 100));
            $value = $baseRate - $baseRateBeforeTax;
        }

        $value = match ($applyType) {
            ProductApplyTypeEnum::PER_ROOM->value => $value,
            ProductApplyTypeEnum::PER_PERSON->value => $value * $numberOfPassengers,
            ProductApplyTypeEnum::PER_NIGHT->value => $value * $numberOfNights,
            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => $value * $numberOfNights * $numberOfPassengers,
            default => $value,
        };

        return $isRounded ? round($value, 2) : $value;
    }

    /**
     * Calculate the rate amount based on the apply type.
     *
     * @todo Can we get the amount of rooms here so we can calculate the fee per room?
     * @param bool $isRounded Whether to round the value.
     * @param float $value The value to be calculated.
     * @param string $applyType The apply type (e.g., PER_PERSON, PER_NIGHT_PER_PERSON).
     * @param int $numberOfNights The number of nights.
     * @param int|null $numberOfPassengers The number of passengers (optional).
     * @return float The calculated rate amount.
     */
    private function calculateFeeAmount(
        bool $isRounded,
        float $baseRate,
        float $value,
        string $applyType,
        string $valueType,
        int $numberOfNights,
        ?int $numberOfPassengers = null
    ): float
    {
        if ($valueType === 'Percentage') {
            $value = $value / 100 * $baseRate; // Convert percentage to absolute value
        }

        $value = match ($applyType) {
            ProductApplyTypeEnum::PER_ROOM->value => $value,
            ProductApplyTypeEnum::PER_PERSON->value => ($value * $numberOfPassengers),
            ProductApplyTypeEnum::PER_NIGHT->value => ($value * $numberOfNights),
            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => ($value * $numberOfNights * $numberOfPassengers),
            default => $value,
        };

        return $isRounded ? round($value, 2) : $value;
    }

    /**
     * Check if the rate is commissionable.
     *
     * @param array $rate The rate to check.
     * @return bool True if the rate is commissionable, false otherwise.
     */
    private function isRateExcluded(array $feeTax, string $ratePlanCode, string $unifiedRoomCode): bool
    {
        if (!is_null($feeTax['rate_code']) && $feeTax['rate_code'] !== $ratePlanCode) {
            return true;
        }

        if (!is_null($feeTax['unified_room_code']) && $feeTax['unified_room_code'] !== $unifiedRoomCode) {
            return true;
        }

        return false;
    }

    /**
     * Check if rate description matches.
     *
     * @param string $description
     * @param string $oldName
     * @return bool
     */
    private function isDescriptionMatching(string $description, string $oldName): bool
    {
        return strcasecmp($description, $oldName) === 0;
    }

    /**
     * Get fee from tax.
     *
     * @param array $taxFee
     * @return array
     */
    private function getRateData(array $taxFee): array
    {
        return [
            'Code' => 'OBE_'.$taxFee['id'],
            'Description' => $taxFee['name'],
            'ObeAction' => $taxFee['action_type'],
            'IsCommissionable' => (bool) $taxFee['commissionable'],
        ];
    }

    /**
     * Get rate amount data.
     *
     * @param array $taxFee
     * @param int $numberOfNights
     * @param int $numberOfPassengers
     * @param string $type
     * @return array
     */
    protected function getRateAmountData($baseRate, array $taxFee, int $numberOfNights, int $numberOfPassengers, string $type): array
    {
        $amountData = [];
        if (isset($taxFee['Amount'])) {
            $amountData = [
                'Amount' => $taxFee['Amount'],
                'RackAmount' => $taxFee['RackAmount'] ?? 0,
            ];
        }

        if (isset($taxFee['net_value']) && isset($taxFee['rack_value'])) {
            $amountData = $this->getAmountData(
                $baseRate,
                $taxFee['net_value'],
                $taxFee['rack_value'],
                $taxFee['apply_type'],
                $taxFee['value_type'],
                $numberOfNights,
                $numberOfPassengers,
                $type,
            );
        }

        return $amountData;
    }

    /**
     * Get amount data from net value.
     *
     * @param float $baseRate
     * @param float $netValue
     * @param float $rackValue
     * @param string $applyType
     * @param string $valueType
     * @param int $numberOfNights
     * @param int $numberOfPassengers
     * @param string $type
     * @return array
     */
    private function getAmountData(
        float $baseRate,
        float $netValue,
        float $rackValue,
        string $applyType,
        string $valueType,
        int $numberOfNights,
        int $numberOfPassengers,
        string $type,
    ): array
    {
        $methodMap = [
            'Fee' => 'calculateFeeAmount',
            'Tax' => 'calculateTaxAmount',
        ];

        if (!isset($methodMap[$type])) {
            throw new \InvalidArgumentException("Invalid type '$type' provided. Expected 'Fee' or 'Tax'.");
        }

        $methodName = $methodMap[$type];

        return [
            'DisplayableRackAmount' => $this->{$methodName}(true, $baseRate, $netValue, $applyType, $valueType, $numberOfNights, $numberOfPassengers),
            'DisplayableAmount' => $this->{$methodName}(true, $baseRate, $netValue, $applyType, $valueType, $numberOfNights, $numberOfPassengers),
            'Amount' => $this->{$methodName}(false, $baseRate, $netValue, $applyType, $valueType, $numberOfNights, $numberOfPassengers),
            'RackAmount' => $this->{$methodName}(false, $baseRate, $rackValue, $applyType, $valueType, $numberOfNights, $numberOfPassengers),
            'ValueType' => $valueType,
        ];
    }

    /**
     * Get fee data from fee.
     *
     * @param array $fee
     * @param int $numberOfPassengers
     * @return void
     */
    private function getFeeData(array $fee, int $numberOfPassengers): array
    {
        return [
            'Type' => $this->getFeeType($fee),
            'MultiplierFee' => $this->getFeeMultiplier($fee, $numberOfPassengers),
            'Level' => $fee['level'] ?? null,
            'CollectedBy' => $fee['collected_by'] === 'Direct' ? 'direct' : 'vendor',
            // 'MultiplierFee' => !$fee['commissionable'] ? 1 :(int) $unitMultiplier,
        ];
    }

    /**
     * Get fee data from fee.
     *
     * @param array $fee
     * @param int $numberOfPassengers
     * @return void
     */
    private function getTaxData(array $tax): array
    {
        return [
            'Type' => $this->getTaxType($tax),
            'Level' => $tax['level'] ?? null,
            'CollectedBy' => $tax['collected_by'] === 'Direct' ? 'direct' : 'vendor',
        ];
    }

    /**
     * Get fee multiplier based on apply type and number of passengers.
     *
     * @param array $fee
     * @param int $numberOfPassengers
     * @return int
     */
    private function getFeeMultiplier(array $fee, int $numberOfPassengers): int
    {
        if ($fee['apply_type'] === ProductApplyTypeEnum::PER_PERSON->value) {
            return $numberOfPassengers;
        }

        return $fee['UnitMultiplier'] ?? 1;
    }

    /**
     * Get fee type based on collected_by.
     *
     * @param array $fee
     * @return string
     */
    private function getFeeType(array $fee): string
    {
        // !$fee['commissionable'] ? 'Exclusive' : 'Inclusive',
        return match ($fee['collected_by']) {
            'Direct' => 'PropertyCollects',
            'Vendor' => 'Inclusive',
            default => 'Exclusive',
        };
    }


    private function getTaxType(array $tax): string
    {
        // !$fee['commissionable'] ? 'Exclusive' : 'Inclusive',
        return match ($tax['collected_by']) {
            'Direct' => 'PropertyCollects',
            'Vendor' => 'Inclusive',
            default => 'Exclusive',
        };
    }

    /**
     * Initialize the Fees array if it doesn't exist.
     *
     * @param array $rate
     * @return void
     */
    private function initializeFeesArray(array &$rate): void
    {
        if (!isset($rate['Fees'])) {
            $rate['Fees'] = [];
        }
    }
}

