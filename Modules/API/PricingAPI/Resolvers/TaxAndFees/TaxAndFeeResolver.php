<?php

namespace Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Enums\ProductApplyTypeEnum;

class TaxAndFeeResolver
{
    public function transformRates(array $rates): array
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

        return $transformedRates;
    }

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
                'Amount' => $tax['@attributes']['Amount'] ?? '',
                'Description' => $tax['TaxDescription']['Text'] ?? '',
            ];
        }

        return $transformedTaxes;
    }

    public function applyRepoTaxFees(array &$transformedRates, $giataCode, $ratePlanCode, $unifiedRoomCode, $numberOfPassengers, $checkinInput, $checkoutInput, $repoTaxFeesInput): void
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

        foreach ($transformedRates as &$rate) {
            if (isset($repoTaxFeesInput[$giataCode])) {

                // Apply edits
                if (isset($repoTaxFees['edit'])) {
                    foreach ($repoTaxFees['edit'] as $editFeeTax) {
                        if (! is_null($editFeeTax['rate_code']) && $editFeeTax['rate_code'] !== $ratePlanCode) {
                            continue;
                        }
                        if (! is_null($editFeeTax['unified_room_code']) && $editFeeTax['unified_room_code'] !== $unifiedRoomCode) {
                            continue;
                        }
                        foreach ($rate['Taxes'] ?? [] as $key => &$tax) {
                            if (is_int($key) || is_string($key)) {
                                if (strcasecmp($tax['Description'], $editFeeTax['old_name']) === 0) {
                                    if (! $editFeeTax['commissionable'] && $editFeeTax['type'] === 'Fee') {
                                        if (! isset($rate['Fees'])) {
                                            $rate['Fees'] = [];
                                        }
                                        // Move from Taxes to Fees
                                        $rate['Fees'][] = [
                                            'Type' => 'Exclusive',
                                            'Code' => 'OBE_'.$editFeeTax['id'],
                                            'Amount' => $editFeeTax['net_value'] ?? $tax['Amount'],
                                            'Description' => $editFeeTax['name'],
                                            'ObeAction' => $editFeeTax['action_type'],
                                            'MultiplierFee' => 1,
                                        ];
                                        unset($rate['Taxes'][$key]);
                                    } else {
                                        $rateData['Type'] = $tax['Type'];
                                        $rateData['Code'] = 'OBE_'.$editFeeTax['id'];
                                        $rateData['Amount'] = match ($editFeeTax['apply_type']) {
                                            ProductApplyTypeEnum::PER_PERSON->value => round(($editFeeTax['net_value'] / $numberOfNights), 3),
                                            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => round(($editFeeTax['net_value'] * $numberOfPassengers / $numberOfNights), 3),
                                            default => $editFeeTax['net_value'],
                                        };
                                        $rateData['Description'] = $editFeeTax['name'];
                                        $rateData['ObeAction'] = $editFeeTax['action_type'];
                                        $rate['Taxes'][] = $rateData;
                                        unset($rate['Taxes'][$key]);
                                    }
                                }
                            }
                        }
                        foreach ($rate['Fees'] ?? [] as $key => &$fee) {
                            if (is_int($key) || is_string($key)) {
                                if (strcasecmp($fee['Description'], $editFeeTax['old_name']) === 0) {
                                    $feeData['Description'] = $editFeeTax['name'];
                                    $feeData['ObeAction'] = $editFeeTax['action_type'];
                                    $feeData['Amount'] = $editFeeTax['net_value'] ?? $fee['Amount'];
                                    $rate['Fees'][] = $feeData;
                                    unset($rate['Fees'][$key]);
                                }
                            }
                        }
                    }
                }

                // Apply updates
                if (isset($repoTaxFees['update'])) {
                    foreach ($repoTaxFees['update'] as $updateFeeTax) {
                        if (! is_null($updateFeeTax['rate_code']) && $updateFeeTax['rate_code'] !== $ratePlanCode) {
                            continue;
                        }
                        if (! is_null($updateFeeTax['unified_room_code']) && $updateFeeTax['unified_room_code'] !== $unifiedRoomCode) {
                            continue;
                        }
                        foreach ($rate['Taxes'] ?? [] as $key => &$tax) {
                            if (is_int($key) || is_string($key)) {
                                if (strcasecmp($tax['Description'], $updateFeeTax['old_name']) === 0) {
                                    // TODO: Check if this is correct or not
                                    // if ($updateFeeTax['type'] === 'Fee') OR if (!$updateFeeTax['commissionable'])
                                    if (! $updateFeeTax['commissionable'] && $updateFeeTax['type'] === 'Fee') {
                                        if (! isset($rate['Fees'])) {
                                            $rate['Fees'] = [];
                                        }
                                        // Move from Taxes to Fees
                                        $rate['Fees'][] = [
                                            'Type' => 'Exclusive',
                                            'Code' => 'OBE_'.$updateFeeTax['id'],
                                            'Amount' => $updateFeeTax['net_value'] ?? $tax['Amount'],
                                            'Description' => $updateFeeTax['name'],
                                            'ObeAction' => $updateFeeTax['action_type'],
                                            'MultiplierFee' => 1,
                                        ];
                                        unset($rate['Taxes'][$key]);
                                    } else {
                                        $rateData['Description'] = $updateFeeTax['name'];
                                        $rateData['ObeAction'] = $updateFeeTax['action_type'];
                                        $rateData['Amount'] = $updateFeeTax['net_value'] ?? $tax['Amount'];
                                        $rate['Taxes'][] = $rateData;
                                    }
                                }
                            }
                        }
                    }
                }

                // Apply additions
                if (isset($repoTaxFees['add'])) {
                    foreach ($repoTaxFees['add'] as $addFeeTax) {

                        if (! is_null($addFeeTax['rate_code']) && $addFeeTax['rate_code'] !== $ratePlanCode) {
                            continue;
                        }

                        if (! is_null($addFeeTax['unified_room_code']) && $addFeeTax['unified_room_code'] !== $unifiedRoomCode) {
                            continue;
                        }

                        $rateData = [
                            'Code' => 'OBE_'.$addFeeTax['id'],
                            // TODO: Check if this is correct or not
                            // Recalculate the nightly fee on a per person basis
                            'Amount' => match ($addFeeTax['apply_type']) {
                                ProductApplyTypeEnum::PER_PERSON->value => round(($addFeeTax['net_value'] / $numberOfNights), 3),
                                ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => round(($addFeeTax['net_value'] * $numberOfPassengers / $numberOfNights), 3),
                                default => $addFeeTax['net_value'],
                            },
                            'Description' => $addFeeTax['name'],
                            'ObeAction' => $addFeeTax['action_type'],
                        ];

                        // TODO: Check if this is correct or not
                        // if ($addFeeTax['type'] === 'Fee') OR if (!$addFeeTax['commissionable'])
                        if (! $addFeeTax['commissionable'] && $addFeeTax['type'] === 'Fee') {
                            if (! isset($rate['Fees'])) {
                                $rate['Fees'] = [];
                            }
                            $rateData['Type'] = 'Exclusive';
                            $rateData['MultiplierFee'] = 1;
                            $rate['Fees'][] = $rateData;
                        } else {
                            $rateData['Type'] = 'Inclusive';
                            $rateData['MultiplierFee'] = $rate['UnitMultiplier'];
                            $rate['Taxes'][] = $rateData;
                        }
                    }
                }

                // Apply deletions
                if (isset($repoTaxFees['delete'])) {
                    foreach ($repoTaxFees['delete'] as $deleteFeeTax) {
                        if (! is_null($deleteFeeTax['rate_code']) && $deleteFeeTax['rate_code'] !== $ratePlanCode) {
                            continue;
                        }
                        if (! is_null($deleteFeeTax['unified_room_code']) && $deleteFeeTax['unified_room_code'] !== $unifiedRoomCode) {
                            continue;
                        }
                        $rate['Taxes'] = array_filter($rate['Taxes'], function ($tax) use ($deleteFeeTax) {
                            return strcasecmp($tax['Description'], $deleteFeeTax['old_name']) !== 0;
                        });
                    }
                }
            } else {
                foreach ($rate['Taxes'] ?? [] as $key => &$tax) {
                    if (Arr::get($tax, 'Type') === 'PropertyCollects') {
                        if (! isset($rate['Fees'])) {
                            $rate['Fees'] = [];
                        }
                        $rate['Fees'][] = $tax;
                        unset($rate['Taxes'][$key]);
                    }
                }
            }
        }
    }

    public function getTransformedBreakdown(array $transformedRates, array $inputFees): array
    {
        $breakdown = [];
        $night = 0;
        $stay = [];
        $fees = [];

        foreach ($transformedRates as $rate) {
            $nightsRate = $rate['UnitMultiplier'];
            $baseFareRate = [
                'amount' => $rate['AmountBeforeTax'],
                'title' => 'Base Rate',
                'type' => 'base_rate',
            ];
            $baseFareRateNight = [
                'amount' => $rate['AmountBeforeTax'],
                'title' => 'Base Rate',
                'type' => 'base_rate',
            ];

            $taxesRate = [];
            $feesRate = [];
            foreach ($rate['Taxes'] as $tax) {
                $code = strtolower($tax['Code']);
                $type = in_array($code, $inputFees) ? 'fee' : 'tax';
                $taxesRate[] = [
                    'type' => $type,
                    'amount' => $tax['Amount'],
                    'title' => $tax['Description'] ?? ($tax['Amount'].' '.$tax['Code']),
                ];
            }
            foreach (Arr::get($rate, 'Fees', []) as $fee) {
                $multiplierFee = Arr::get($fee, 'MultiplierFee', 1);
                $code = strtolower($fee['Code']);
                $type = 'fee';
                $feesRate[] = [
                    'type' => $type,
                    'amount' => $fee['Amount'] * $multiplierFee,
                    'title' => $fee['Description'] ?? ($fee['Amount'].' '.$fee['Code']),
                    'multiplier' => $multiplierFee,
                ];
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
}
