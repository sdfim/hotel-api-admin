<?php

namespace Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\API\PricingAPI\Resolvers\Helpers\Filters;
use Modules\API\PricingAPI\Resolvers\Helpers\ServiceCalculationHelper;
use Modules\Enums\ProductApplyTypeEnum;

class BaseTaxAndFeeResolver
{
    /**
     * Filters and returns the list of default extra fees for a specific supplier at the hotel level.
     *
     * @param  array  $informativeFees  List of all default extra fees.
     * @param  int  $giataId  The GIATA ID to filter by.
     * @param  int  $supplierId  The supplier ID to filter by.
     * @param  string|null  $checkin  Check-in date (Y-m-d) used to evaluate start/end date filters (optional).
     * @param  string|null  $checkout  Check-out date (Y-m-d) used to evaluate start/end date filters (optional).
     * @param  array  $occupancy  Occupancy array used to evaluate age restrictions (optional).
     * @return array Filtered array of extra fees at the hotel level (room_id and rate_id are null).
     */
    public function getInformativeFeesHotelLevel(
        array $informativeFees,
        int $giataId,
        int $supplierId,
        ?string $checkin = null,
        ?string $checkout = null,
        array $occupancy = []
    ): array {
        $fees = [];

        // Collect candidate fees matching supplier and hotel-level criteria
        $candidates = array_values(array_filter(Arr::get($informativeFees, $giataId, []), function ($fee) use ($supplierId) {
            return ($fee['supplier_id'] === $supplierId || $fee['supplier_id'] === null)
                && $fee['room_id'] === null && $fee['rate_id'] === null;
        }));

        if (empty($candidates)) {
            return [];
        }

        // Prepare checkin/checkout Carbon instances; fallback to today/today+1 if not provided
        $checkinDate = $checkin ? Carbon::parse($checkin) : Carbon::now();
        $checkoutDate = $checkout ? Carbon::parse($checkout) : Carbon::parse($checkinDate)->addDay();

        // numberOfNights for filterRepoServices: at least 1
        $numberOfNights = max(1, $checkoutDate->diffInDays($checkinDate));

        // Use the existing helper to filter by date, nights, and age/occupancy
        $filtered = Filters::filterRepoServices($candidates, $checkinDate, $checkoutDate, $numberOfNights, $occupancy);

        foreach ($filtered as $fee) {
            $fees[] = Arr::only($fee, [
                'description',
                'value_type',
                'apply_type',
                'net_value',
                'rack_value',
                'collected_by',
            ]);
        }

        return $fees;
    }

    /**
     * Filters and returns the list of default extra fees for a specific supplier and room.
     *
     * @param  array  $informativeFees  List of all default extra fees.
     * @param  int  $giataId  The GIATA ID to filter by.
     * @param  int  $supplierId  The supplier ID to filter by.
     * @param  string  $unifiedRoomCode  The unified room code to filter by.
     * @param  string  $RateCode  The rate code to filter by.
     * @param  array  $occupancy  Occupancy array used to evaluate age restrictions (optional).
     * @param  string|null  $checkin  Check-in date (Y-m-d) used to evaluate start/end date filters (optional).
     * @param  string|null  $checkout  Check-out date (Y-m-d) used to evaluate start/end date filters (optional).
     * @return array Filtered array of extra fees matching the supplier and room code.
     */
    public function getInformativeFeesRoomLevel(
        array $informativeFees,
        int $giataId,
        int $supplierId,
        string $unifiedRoomCode,
        string $RateCode,
        array $occupancy = [],
        ?string $checkin = null,
        ?string $checkout = null
    ): array {
        $fees = [];

        // First collect candidate fees matching supplier and (room or rate)
        $candidates = array_values(array_filter(Arr::get($informativeFees, $giataId, []), function ($fee) use ($supplierId, $unifiedRoomCode, $RateCode) {
            return (Arr::get($fee, 'supplier_id') === $supplierId || ! Arr::get($fee, 'supplier_id'))
                && (Arr::get($fee, 'unified_room_code') === $unifiedRoomCode || Arr::get($fee, 'rate_code') === $RateCode);
        }));

        if (empty($candidates)) {
            return [];
        }

        // Prepare checkin/checkout Carbon instances; fallback to today/today+1 if not provided
        $checkinDate = $checkin ? Carbon::parse($checkin) : Carbon::now();
        $checkoutDate = $checkout ? Carbon::parse($checkout) : Carbon::parse($checkinDate)->addDay();

        // numberOfNights for filterRepoServices: at least 1
        $numberOfNights = max(1, $checkoutDate->diffInDays($checkinDate));

        // Use the existing helper to filter by date, nights and age/occupancy
        $filtered = Filters::filterRepoServices($candidates, $checkinDate, $checkoutDate, $numberOfNights, $occupancy);

        foreach ($filtered as $fee) {
            $fees[] = Arr::only($fee, [
                'description',
                'value_type',
                'apply_type',
                'net_value',
                'rack_value',
            ]);
        }

        return $fees;
    }

    /**
     * Apply repo tax fees to the transformed rates.
     *
     * @param  array  $transformedRates  The transformed rates.
     * @param  string  $giataCode  The GIATA code.
     * @param  string  $ratePlanCode  The rate plan code.
     * @param  string  $unifiedRoomCode  The unified room code.
     * @param  int  $numberOfPassengers  The number of passengers.
     * @param  string  $checkinInput  The check-in date.
     * @param  string  $checkoutInput  The check-out date.
     * @param  array  $repoTaxFeesInput  The repo tax fees input.
     */
    public function applyRepoTaxFees(array &$transformedRates, $giataCode, $ratePlanCode, $unifiedRoomCode, $numberOfPassengers, $checkinInput, $checkoutInput, $repoTaxFeesInput, $occupancy, $hotelCurrency = 'USD'): void
    {
        $repoTaxFees = Arr::get($repoTaxFeesInput, $giataCode, []);

        $checkin = Carbon::parse($checkinInput);
        $checkout = Carbon::parse($checkoutInput);

        // Filter tax & fees by apply type
        foreach ($repoTaxFees as $key => $taxFees) {
            $repoTaxFees[$key] = Filters::filterRepoServices($taxFees, $checkin, $checkout, 0, $occupancy);
        }

        foreach ($transformedRates as &$rate) {

            $numberOfNights = (int) Carbon::parse(Arr::get($rate, 'effective_date'))->diffInDays(Carbon::parse(Arr::get($rate, 'expire_date')));

            $this->initializeFeesArray($rate);

            if (isset($repoTaxFeesInput[$giataCode]) && ! empty($repoTaxFeesInput[$giataCode])) {

                //                dd($repoTaxFeesInput[$giataCode]);

                // Apply edits
                if (isset($repoTaxFees['edit'])) {
                    foreach ($repoTaxFees['edit'] as $editFeeTax) {
                        //                        if ($this->isRateExcluded($editFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                        //                            continue;
                        //                        }

                        if ($editFeeTax['apply_type'] === 'per_room') {
                            if ($editFeeTax['start_date'] || $editFeeTax['end_date']) {
                                $baseRate = $rate['amount_before_tax'] ?? 0;
                            } else {
                                $baseRate = $rate['total_amount_before_tax'] ?? $rate['amount_before_tax'] ?? 0;
                            }
                        } else {
                            $baseRate = $rate['amount_before_tax'] ?? 0;
                        }

                        foreach ($rate['taxes'] ?? [] as $key => &$tax) {

                            if (! is_int($key) && ! is_string($key)) {
                                continue;
                            }

                            if (! $this->isDescriptionMatching($tax['description'], $editFeeTax['old_name'])) {
                                continue;
                            }

                            $serviceNumberOfNights = ServiceCalculationHelper::calculateServiceAvailableNights($editFeeTax, $checkin, $checkout, $numberOfNights);
                            $rateData = $this->getRateData($editFeeTax);

                            if ($editFeeTax['type'] === 'Fee') {
                                $feeData = $this->getFeeData($editFeeTax, $serviceNumberOfNights, $numberOfPassengers, $occupancy);
                                $rateData = array_merge($rateData, $feeData);
                                $rateData['multiplier_fee'] = ServiceCalculationHelper::getServiceMultiplier($editFeeTax, $serviceNumberOfNights, $occupancy, 'Fee');
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $editFeeTax,
                                    $serviceNumberOfNights,
                                    'Fee',
                                    $hotelCurrency,
                                    $occupancy
                                );
                                $rateData = array_merge($rateData, $amountData);
                                $rate['fees'][] = $rateData;
                                unset($rate['taxes'][$key]);
                            } else {
                                $rateData = array_merge($rateData, $this->getTaxData($editFeeTax));
                                $rateData['multiplier_fee'] = ServiceCalculationHelper::getServiceMultiplier($editFeeTax, $serviceNumberOfNights, $occupancy, 'Tax');
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $editFeeTax,
                                    $serviceNumberOfNights,
                                    'Tax',
                                    $hotelCurrency,
                                    $occupancy
                                );
                                $rateData = array_merge($rateData, $amountData);
                                $rate['taxes'][] = $rateData;
                                unset($rate['taxes'][$key]);
                            }
                        }

                        foreach ($rate['fees'] ?? [] as $key => &$fee) {
                            if (! is_int($key) && ! is_string($key)) {
                                continue;
                            }

                            if (! $this->isDescriptionMatching($fee['description'], $editFeeTax['old_name'])) {
                                continue;
                            }

                            $feeData = $this->getRateData($editFeeTax);
                            $amountData = $this->getRateAmountData(
                                $baseRate,
                                $editFeeTax,
                                $numberOfNights,
                                'Fee',
                                $editFeeTax['currency'],
                                $occupancy
                            );
                            $feeData = array_merge($feeData, $amountData);
                            $rate['fees'][] = $feeData;
                            unset($rate['fees'][$key]);
                        }
                    }
                }

                // Apply updates
                if (isset($repoTaxFees['update'])) {
                    foreach ($repoTaxFees['update'] as $updateFeeTax) {
                        //                        if ($this->isRateExcluded($updateFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                        //                            continue;
                        //                        }

                        $finalFeeOrTax = [];

                        foreach ($rate['taxes'] as $key => $tax) {

                            if (! is_int($key) && ! is_string($key)) {
                                continue;
                            }

                            if (! $this->isDescriptionMatching($tax['description'], $updateFeeTax['old_name'])) {
                                continue;
                            }

                            $serviceNumberOfNights = ServiceCalculationHelper::calculateServiceAvailableNights($updateFeeTax, $checkin, $checkout, $numberOfNights);
                            $rateData = $this->getRateData($updateFeeTax);

                            if ($updateFeeTax['apply_type'] === 'per_room') {
                                if ($updateFeeTax['start_date'] || $updateFeeTax['end_date']) {
                                    $baseRate = $rate['amount_before_tax'] ?? 0;
                                } else {
                                    $baseRate = $rate['total_amount_before_tax'] ?? $rate['amount_before_tax'] ?? 0;
                                }
                            } else {
                                $baseRate = $rate['amount_before_tax'] ?? 0;
                            }

                            foreach (['amount', 'displayable_amount', 'rack_amount', 'displayable_rack_amount'] as $field) {
                                if (isset($tax[$field])) {
                                    $rateData[$field] = $tax[$field];
                                }
                            }

                            if ($updateFeeTax['type'] === 'Fee') {
                                $feeData = $this->getFeeData($updateFeeTax, $serviceNumberOfNights, $numberOfPassengers, $occupancy);
                                $rateData = array_merge($rateData, $feeData);
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $rateData,
                                    $serviceNumberOfNights,
                                    'Fee',
                                    $updateFeeTax['currency'],
                                    $occupancy
                                );
                                foreach (['amount', 'displayable_amount', 'rack_amount', 'displayable_rack_amount'] as $field) {
                                    if (isset($amountData['amount'])) {
                                        $rateData[$field] = (float) $amountData['amount'];
                                    }
                                }
                            } else {
                                $rateData = array_merge($rateData, $this->getTaxData($updateFeeTax));
                                $rateData['Type'] = 'Inclusive';
                                $rateData['multiplier_fee'] = ServiceCalculationHelper::getServiceMultiplier($updateFeeTax, $serviceNumberOfNights, $occupancy, 'Tax');
                                $amountData = $this->getRateAmountData(
                                    $baseRate,
                                    $updateFeeTax,
                                    $serviceNumberOfNights,
                                    'Tax',
                                    $updateFeeTax['currency'],
                                    $occupancy
                                );
                                $rateData = array_merge($rateData, $amountData);
                            }

                            if (empty($finalFeeOrTax)) {
                                $finalFeeOrTax = $rateData;
                            } else {
                                $finalFeeOrTax['amount'] += $rateData['amount'];
                                $finalFeeOrTax['displayable_amount'] += $rateData['displayable_amount'];
                                $finalFeeOrTax['rack_amount'] += $rateData['rack_amount'];
                                $finalFeeOrTax['displayable_rack_amount'] += $rateData['displayable_rack_amount'];
                                $finalFeeOrTax['multiplier_fee'] += $rateData['multiplier_fee'];
                            }

                            unset($rate['taxes'][$key]);
                        }

                        if (! empty($finalFeeOrTax)) {
                            $arrType = $updateFeeTax['type'] === 'Fee' ? 'fees' : 'taxes';
                            $rate[$arrType][] = $finalFeeOrTax;
                        }
                    }
                }

                // Apply included/hilton inclusive taxes
                if (isset($repoTaxFees['included'])) {
                    foreach ($repoTaxFees['included'] as $addFeeTax) {
                        //                        if ($this->isRateExcluded($addFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                        //                            continue;
                        //                        }

                        $rateData = $this->getRateData($addFeeTax);
                        $baseRate = $rate['amount_before_tax'] ?? 0;

                        $rateData = array_merge($rateData, $this->getTaxData($addFeeTax));
                        $rateData['multiplier_fee'] = $numberOfNights;
                        $amountData = $this->getRateAmountData(
                            $baseRate,
                            $addFeeTax,
                            $numberOfNights,
                            'Tax',
                            Arr::get($addFeeTax, 'currency', 'USD'),
                            $occupancy
                        );
                        $rateData = array_merge($rateData, $amountData);
                        $rateData['Type'] = 'Inclusive';
                        $rate['taxes'][] = $rateData;
                    }
                }

                // Apply additions
                if (isset($repoTaxFees['add'])) {
                    foreach ($repoTaxFees['add'] as $addFeeTax) {
                        //                        if ($this->isRateExcluded($addFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                        //                            continue;
                        //                        }

                        if ($addFeeTax['fee_category'] !== 'mandatory') {
                            continue;
                        }

                        $serviceNumberOfNights = ServiceCalculationHelper::calculateServiceAvailableNights($addFeeTax, $checkin, $checkout, $numberOfNights);
                        $rateData = $this->getRateData($addFeeTax);

                        if ($addFeeTax['apply_type'] === 'per_room') {
                            if ($addFeeTax['start_date'] || $addFeeTax['end_date']) {
                                $baseRate = $rate['amount_before_tax'] ?? 0;
                            } else {
                                $baseRate = $rate['total_amount_before_tax'] ?? $rate['amount_before_tax'] ?? 0;
                            }
                        } else {
                            $baseRate = $rate['amount_before_tax'] ?? 0;
                        }

                        if ($addFeeTax['type'] === 'Fee') {
                            $feeData = $this->getFeeData($addFeeTax, $serviceNumberOfNights, $numberOfPassengers, $occupancy);
                            $rateData = array_merge($rateData, $feeData);
                            $amountData = $this->getRateAmountData(
                                $baseRate,
                                $addFeeTax,
                                $serviceNumberOfNights,
                                'Fee',
                                Arr::get($addFeeTax, 'currency', 'USD'),
                                $occupancy
                            );
                            $rateData = array_merge($rateData, $amountData);
                            $rate['fees'][] = $rateData;
                        } else {
                            $rateData = array_merge($rateData, $this->getTaxData($addFeeTax));
                            $rateData['multiplier_fee'] = ServiceCalculationHelper::getServiceMultiplier($addFeeTax, $serviceNumberOfNights, $occupancy, 'Tax');
                            $amountData = $this->getRateAmountData(
                                $baseRate,
                                $addFeeTax,
                                $serviceNumberOfNights,
                                'Tax',
                                Arr::get($addFeeTax, 'currency', 'USD'),
                                $occupancy
                            );
                            $rateData = array_merge($rateData, $amountData);
                            $rate['taxes'][] = $rateData;
                        }
                    }
                }

                // Apply deletions
                if (isset($repoTaxFees['delete'])) {

                    foreach ($repoTaxFees['delete'] as $deleteFeeTax) {
                        //                        if ($this->isRateExcluded($deleteFeeTax, $ratePlanCode, $unifiedRoomCode)) {
                        //                            continue;
                        //                        }

                        $rate['taxes'] = array_filter($rate['taxes'], function ($tax) use ($deleteFeeTax) {
                            return ! $this->isDescriptionMatching($tax['description'], $deleteFeeTax['old_name']);
                        });
                    }
                }
            }
        }

        foreach ($rate['taxes'] ?? [] as $key => &$tax) {
            $multiplierFee = Arr::get($tax, 'multiplier_fee', 1);

            if (isset($tax['amount'])) {
                $tax['amount'] *= $multiplierFee;
            }
            if (Arr::get($tax, 'obe_action') === 'included') {
                $rate['amount_before_tax'] -= $tax['amount'];
                $rate['total_amount_before_tax'] -= $tax['amount'] * $numberOfNights;
            }
            if (isset($tax['rack_amount'])) {
                $tax['rack_amount'] *= $multiplierFee;
            }
            if (isset($tax['displayable_amount'])) {
                $tax['displayable_amount'] *= $multiplierFee;
            }
            if (isset($tax['displayable_rack_amount'])) {
                $tax['displayable_rack_amount'] *= $multiplierFee;
            }

            $tax['multiplier_fee'] = 1;
        }
    }

    /**
     * Initialize the Fees array if it doesn't exist.
     */
    private function initializeFeesArray(array &$rate): void
    {
        if (! isset($rate['fees'])) {
            $rate['fees'] = [];
        }
    }

    /**
     * Check if the rate is commissionable.
     *
     * @return bool True if the rate is commissionable, false otherwise.
     */
    private function isRateExcluded(array $feeTax, string $ratePlanCode, string $unifiedRoomCode): bool
    {
        if (! is_null($feeTax['rate_code']) && $feeTax['rate_code'] !== $ratePlanCode) {
            return true;
        }

        if (! is_null($feeTax['unified_room_code']) && $feeTax['unified_room_code'] !== $unifiedRoomCode) {
            return true;
        }

        return false;
    }

    /**
     * Check if rate description matches.
     */
    private function isDescriptionMatching(string $description, string $oldName): bool
    {
        return strcasecmp($description, $oldName) === 0;
    }

    /**
     * Get fee from tax.
     */
    private function getRateData(array $taxFee): array
    {
        return [
            'code' => 'OBE_'.($taxFee['id'] ?? 'unknown'),
            'description' => $taxFee['name'] ?? 'Unknown',
            'obe_action' => $taxFee['action_type'] ?? 'add',
            'is_commissionable' => (bool) ($taxFee['commissionable'] ?? false),
        ];
    }

    /**
     * Get rate amount data.
     */
    protected function getRateAmountData(
        $baseRate,
        array $taxFee,
        int $numberOfNights,
        string $type,
        ?string $currency = null,
        ?array $occupancy = []
    ): array {
        $amountData = [];
        if (isset($taxFee['Amount'])) {
            $amountData = [
                'amount' => $taxFee['Amount'],
                'rack_amount' => $taxFee['RackAmount'] ?? 0,
            ];
        }
        if (isset($taxFee['net_value']) && isset($taxFee['rack_value'])) {
            $amountData = $this->getAmountData(
                $taxFee,
                $baseRate,
                $numberOfNights,
                $type,
                $occupancy
            );
        }
        $amountData['currency'] = $currency ?? 'USD';

        return $amountData;
    }

    /**
     * Get amount data from net value.
     */
    private function getAmountData(
        array $taxFee,
        float $baseRate,
        int $numberOfNights,
        string $type,
        ?array $occupancy = []
    ): array {

        $netValue = $taxFee['net_value'];
        $rackValue = $taxFee['rack_value'];
        $applyType = $taxFee['apply_type'];
        $valueType = $taxFee['value_type'];

        $eligiblePersons = Filters::getEligiblePersonsCount($taxFee, $occupancy);

        $map = match ("{$valueType}_{$type}") {
            'Percentage_Tax' => [$this, 'calculateTaxAmountPercentage'],
            'Amount_Tax' => [$this, 'calculateTaxAmount'],
            'Percentage_Fee' => [$this, 'calculateFeeAmountPercentage'],
            'Amount_Fee' => [$this, 'calculateFeeAmount'],
            default => throw new \InvalidArgumentException("Invalid combination of type '$type' and valueType '$valueType' provided."),
        };

        $fn = $map;

        /** @var callable $fn */
        return [
            'displayable_rack_amount' => $fn(true, $baseRate, $rackValue, $applyType, $valueType, $numberOfNights, $eligiblePersons),
            'displayable_amount' => $fn(true, $baseRate, $netValue, $applyType, $valueType, $numberOfNights, $eligiblePersons),
            'amount' => $fn(false, $baseRate, $netValue, $applyType, $valueType, $numberOfNights, $eligiblePersons),
            'rack_amount' => $fn(false, $baseRate, $rackValue, $applyType, $valueType, $numberOfNights, $eligiblePersons),
            'value_type' => $valueType,
        ];
    }

    /**
     * Get fee data from fee.
     */
    private function getFeeData(array $fee, int $numberOfNights, int $numberOfPassengers, array $occupancy = []): array
    {
        return [
            'type' => $this->getFeeType($fee),
            'multiplier_fee' => ServiceCalculationHelper::getServiceMultiplier($fee, $numberOfNights, $occupancy, 'Fee'),
            'level' => $fee['level'] ?? null,
            'collected_by' => $fee['collected_by'],
            // 'multiplier_fee' => !$fee['commissionable'] ? 1 :(int) $unitMultiplier,
        ];
    }

    /**
     * Get fee data from fee.
     */
    private function getTaxData(array $tax): array
    {
        return [
            'type' => $this->getTaxType($tax),
            'level' => $tax['level'] ?? null,
            'collected_by' => $tax['collected_by'],
            'start_date' => $tax['start_date'] ?? null,
            'end_date' => $tax['end_date'] ?? null,
        ];
    }

    /**
     * Get fee type based on collected_by.
     */
    private function getFeeType(array $fee): string
    {
        // !$fee['commissionable'] ? 'Exclusive' : 'Inclusive',
        return match ($fee['collected_by']) {
            'direct' => 'PropertyCollects',
            'vendor' => 'Inclusive',
            default => 'Exclusive',
        };
    }

    private function getTaxType(array $tax): string
    {
        // !$fee['commissionable'] ? 'Exclusive' : 'Inclusive',
        return match ($tax['collected_by']) {
            'direct' => 'PropertyCollects',
            'vendor' => 'Inclusive',
            default => 'Exclusive',
        };
    }

    /**
     * Determine if a tax is commissionable based on CollectedBy and IsCommissionable flags.
     */
    protected function isTaxCommissionable(array $tax): bool
    {
        if (strtolower((string) Arr::get($tax, 'CollectedBy')) === 'direct') {
            return false;
        }

        return (bool) Arr::get($tax, 'IsCommissionable', false);
    }

    /**
     * Calculate the rate amount based on the apply type.
     * Returns the calculated amount for the _whole stay_.
     *
     * @param  bool  $isRounded  Whether to round the value.
     * @param  float  $value  The value to be calculated.
     * @param  string  $applyType  The apply type (e.g., PER_PERSON, PER_NIGHT_PER_PERSON).
     * @param  string  $valueType  The value type (e.g., Percentage, Fixed).
     * @param  int  $numberOfNights  The number of nights.
     * @param  int|null  $numberOfPassengers  The number of passengers (optional).
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
    ): float {

        $value = match ($applyType) {
            ProductApplyTypeEnum::PER_PERSON->value => $value * $numberOfPassengers,
            ProductApplyTypeEnum::PER_NIGHT->value => $value * $numberOfNights,
            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => $value * $numberOfPassengers * $numberOfNights,
            default => $value,
        };

        $value /= $numberOfNights;

        return $isRounded ? round($value, 3) : $value;
    }

    /**
     * Calculate the rate amount based on the apply type.
     * Returns the calculated amount for the whole stay.
     *
     * @param  bool  $isRounded  Whether to round the value.
     * @param  float  $value  The value to be calculated.
     * @param  string  $applyType  The apply type (e.g., PER_PERSON, PER_NIGHT_PER_PERSON).
     * @param  string  $valueType  The value type (e.g., Percentage, Fixed).
     * @param  int  $numberOfNights  The number of nights.
     * @param  int|null  $numberOfPassengers  The number of passengers (optional).
     * @return float The calculated rate amount.
     */
    private function calculateTaxAmountPercentage(
        bool $isRounded,
        float $baseRate,
        float $value,
        string $applyType,
        string $valueType,
        int $numberOfNights,
        ?int $numberOfPassengers = null
    ): float {
        $tax = $value / 100;

        $taxToApply = match ($applyType) {
            ProductApplyTypeEnum::PER_ROOM->value => $tax,
            ProductApplyTypeEnum::PER_NIGHT->value => $tax,
            ProductApplyTypeEnum::PER_PERSON->value => $tax * $numberOfPassengers,
            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => $tax * $numberOfPassengers,
            default => $tax,
        };

        $value = ($baseRate * $taxToApply);

        if ($applyType === ProductApplyTypeEnum::PER_ROOM->value) {
            $value = $value / $numberOfNights;
        }

        return $isRounded ? round($value, 2) : $value;
    }

    /**
     * Calculate the rate amount based on the apply type.
     * Returns the calculated amount for the whole stay.
     *
     * @todo Can we get the amount of rooms here so we can calculate the fee per room?
     *
     * @param  bool  $isRounded  Whether to round the value.
     * @param  float  $value  The value to be calculated.
     * @param  string  $applyType  The apply type (e.g., PER_PERSON, PER_NIGHT_PER_PERSON).
     * @param  int  $numberOfNights  The number of nights.
     * @param  int|null  $numberOfPassengers  The number of passengers (optional).
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
    ): float {
        return $isRounded ? round($value, 2) : $value;
    }

    /**
     * Calculate the rate amount based on the apply type.
     * Returns the calculated amount for the whole stay.
     *
     * @todo Can we get the amount of rooms here so we can calculate the fee per room?
     *
     * @param  bool  $isRounded  Whether to round the value.
     * @param  float  $value  The value to be calculated.
     * @param  string  $applyType  The apply type (e.g., PER_PERSON, PER_NIGHT_PER_PERSON).
     * @param  int  $numberOfNights  The number of nights.
     * @param  int|null  $numberOfPassengers  The number of passengers (optional).
     * @return float The calculated rate amount.
     */
    private function calculateFeeAmountPercentage(
        bool $isRounded,
        float $baseRate,
        float $value,
        string $applyType,
        string $valueType,
        int $numberOfNights,
        ?int $numberOfPassengers = null
    ): float {
        $value = $value / 100 * $baseRate; // Convert percentage to absolute value

        return $isRounded ? round($value, 2) : $value;
    }

    /**
     * Get the transformed breakdown of rates.
     *
     * @param  array  $transformedRates  The transformed rates.
     * @param  Carbon|null  $checkin  The check-in date.
     * @param  Carbon|null  $checkout  The check-out date.
     * @return array The transformed breakdown of rates.
     */
    public function getBreakdown(array $transformedRates, ?Carbon $checkin = null, ?Carbon $checkout = null): array
    {
        $breakdown = [];
        $night = 0;
        $stay = [];
        $fees = [];

        foreach ($transformedRates as $rate) {
            // Adjust the base amount by removing inclusive taxes if applicable
            $totalInclusiveTax = 0;
            foreach ($rate['taxes'] as $tax) {
                if ($tax['type'] === 'Inclusive') {
                    $totalInclusiveTax += (float) $tax['amount'];
                }
            }

            if ($rate['amount_before_tax'] == $rate['amount_after_tax'] && $totalInclusiveTax > 0) {
                $rate['amount_before_tax'] -= $totalInclusiveTax;
            }

            $fareRate = [
                'amount' => $rate['amount_before_tax'],
                'rack_amount' => $rate['amount_before_tax'],
                'title' => 'Base Rate',
                'level' => 'rate',
                'collected_by' => 'vendor',
                'type' => 'base_rate',
            ];

            $nightsRate = $rate['unit_multiplier'];
            $baseFareRateNight = array_merge($fareRate, [
                'amount' => round($rate['amount_before_tax'], 2),
                'rack_amount' => round($rate['amount_before_tax'], 2),
            ]);

            $taxesRate = Arr::get($rate, 'taxes', []);
            $feesRate = Arr::get($rate, 'fees', []);

            for ($i = 0; $i < $nightsRate; $i++) {
                $breakdown[$night][] = $baseFareRateNight;

                // Filter taxes that apply to this specific night
                if ($checkin && $checkout) {
                    $currentNightDate = $checkin->copy()->addDays($night);
                    $applicableTaxes = ServiceCalculationHelper::filterTaxesForDate($taxesRate, $currentNightDate);
                    $breakdown[$night] = array_merge($breakdown[$night], $applicableTaxes);
                } else {
                    // Fallback to original behavior if no dates provided
                    $breakdown[$night] = array_merge($breakdown[$night], $taxesRate);
                }

                $night++;
            }

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
