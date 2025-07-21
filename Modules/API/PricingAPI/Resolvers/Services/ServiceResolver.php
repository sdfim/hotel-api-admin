<?php

namespace Modules\API\PricingAPI\Resolvers\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Enums\ProductApplyTypeEnum;

class ServiceResolver
{
    /**
     * Apply repo services to transformed rates.
     *
     * @param array $transformedRates
     * @param string $giataCode
     * @param string $ratePlanCode
     * @param string $unifiedRoomCode
     * @param int $numberOfPassengers
     * @param string $checkinInput
     * @param string $checkoutInput
     * @param array $repoServicesInput
     */
    public function applyRepoService(
        array &$transformedRates,
        string $giataCode,
        string $ratePlanCode,
        string $unifiedRoomCode,
        int    $numberOfPassengers,
        string $checkinInput,
        string $checkoutInput,
        array  $repoServicesInput,
        array  $occupancy = []
    ): void
    {
        // Get services for the specific hotel (giata)
        $repoServices = Arr::get($repoServicesInput, $giataCode, []);

        $numberOfNights = array_sum(array_column($transformedRates, 'UnitMultiplier'));

        $checkin = Carbon::parse($checkinInput);
        $checkout = Carbon::parse($checkoutInput);

        // Filter services based on date / age / nights restrictions
        $repoServices = $this->filterRepoServices($repoServices, $checkin, $checkout, $numberOfNights, $occupancy);

        // Passed as reference so no need to return
        $transformedRates = $this->processTransformedRates(
            $transformedRates,
            $repoServices,
            $ratePlanCode,
            $unifiedRoomCode,
            $numberOfNights,
            $numberOfPassengers
        );
    }

    /**
     * Filter repo services.
     *
     * @param array  $services
     * @param Carbon $checkin
     * @param Carbon $checkout
     * @param int    $numberOfNights
     * @param array  $occupancy
     * @return array Filtered list of services (array of service arrays)
     */
    protected function filterRepoServices(array $services, Carbon $checkin, Carbon $checkout, int $numberOfNights, array $occupancy): array
    {
        return array_values(array_filter($services, function ($service) use ($checkin, $checkout, $numberOfNights, $occupancy) {
            // 1. Date overlap check (stay period overlaps service period)
            $startDate = Arr::get($service, 'start_date');
            $endDate   = Arr::get($service, 'end_date');

            if ($startDate && $endDate) {
                $startDate = Carbon::parse($startDate);
                $endDate   = Carbon::parse($endDate);

                // If no overlap, exclude
                if ($checkout <= $startDate || $checkin >= $endDate->addDay()) { // addDay to make end inclusive
                    return false;
                }
            }

            // 2. Nights restriction
            $minNight = (int) Arr::get($service, 'min_night_stay', 0);
            $maxNight = (int) Arr::get($service, 'max_night_stay', 0);
            if ($minNight > 0 && $numberOfNights < $minNight) {
                return false;
            }
            if ($maxNight > 0 && $numberOfNights > $maxNight) {
                return false;
            }

            // 3. Age restriction – exclude child-specific services when no eligible children.
            $ageFromRaw = Arr::get($service, 'age_from', null);
            $ageToRaw   = Arr::get($service, 'age_to', null);
            $ageFrom = is_null($ageFromRaw) ? null : (int) $ageFromRaw;
            $ageTo   = is_null($ageToRaw) ? null : (int) $ageToRaw;

            if (!is_null($ageFrom) && !is_null($ageTo) && $ageTo > $ageFrom && $ageTo < 18) {
                $hasChildInRange = false;
                foreach ($occupancy as $occ) {
                    foreach (($occ['children_ages'] ?? []) as $childAge) {
                        if ($childAge >= $ageFrom && $childAge <= $ageTo) {
                            $hasChildInRange = true;
                            break 2; // exit both loops
                        }
                    }
                }
                if (! $hasChildInRange) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * Process transformed rates and apply repo services.
     *
     * @param array $transformedRates
     * @param array $servicesFiltered List of services already filtered
     * @param string $ratePlanCode
     * @param string $unifiedRoomCode
     * @param int $numberOfNights
     * @param int $numberOfPassengers
     * @return array
     */
    protected function processTransformedRates(
        array $transformedRates,
        array $servicesFiltered,
        string $ratePlanCode,
        string $unifiedRoomCode,
        int $numberOfNights,
        int $numberOfPassengers
    ): array
    {
        foreach ($transformedRates as &$rate) {
            if (! empty($servicesFiltered)) {
                foreach ($servicesFiltered as $service) {

                    if ($this->isServiceExcluded($service, $ratePlanCode, $unifiedRoomCode)) {
                        continue;
                    }

                    $isCommissionable = $service['commissionable'];

                    $amount = $this->calculateRateAmount(
                        $service['total_net'] ?? 0,
                        $service['apply_type'],
                        $numberOfNights,
                        $numberOfPassengers
                    );
                    $rackAmount = $this->calculateRateAmount(
                        $service['cost'] ?? 0,
                        $service['apply_type'],
                        $numberOfNights,
                        $numberOfPassengers
                    );

                    $rateData = [
                        'Code' => 'OBE_' . $service['id'],
                        'Amount' => $amount,
                        'RackAmount' => $rackAmount,
                        'DisplayableAmount' => $amount,
                        'DisplayableRackAmount' => $rackAmount,
                        'Description' => $service['name'],
                        'IsCommissionable' => (bool) $isCommissionable,
                        'Type' => $isCommissionable ? 'Inclusive' : 'Exclusive',
                        'MultiplierFee' => 1,
                        'CollectedBy' => $service['collected_by'] ?? null,
                    ];

                    $this->initializeFeesArray($rate);
                    $rate['Fees'][] = $rateData;
                }
            } else {
                foreach ($rate['Taxes'] ?? [] as $key => &$tax) {
                    if (Arr::get($tax, 'Type') === 'PropertyCollects') {
                        $this->initializeFeesArray($rate);
                        $rate['Fees'][] = $tax;
                        unset($rate['Taxes'][$key]);
                    }
                }
            }
        }

        return $transformedRates;
    }

    /**
     * Check if the service is excluded based on rate code miss matching, the unified room code miss matching and if it is not mandatory.
     *
     * @param array $service
     * @param string $ratePlanCode
     * @param string $unifiedRoomCode
     * @return bool
     */
    protected function isServiceExcluded(array $service, string $ratePlanCode, string $unifiedRoomCode): bool
    {
        if ($this->hasDifferentRateCode($service['rate_code'], $ratePlanCode)) {
            return true;
        }

        if ($this->hasDifferentUnifiedRoomCode($service['unified_room_code'], $unifiedRoomCode)) {
            return true;
        }

        if ($this->isNotMandatory($service['auto_book'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if the rate code is different.
     *
     * @param string|null $serviceRateCode
     * @param string $ratePlanCode
     * @return bool
     */
    protected function hasDifferentRateCode(?string $serviceRateCode, string $ratePlanCode): bool
    {
        return !is_null($serviceRateCode) && $serviceRateCode !== $ratePlanCode;
    }

    /**
     * Check if the unified room code is different.
     *
     * @param string|null $serviceUnifiedRoomCode
     * @param string $unifiedRoomCode
     * @return bool
     */
    protected function hasDifferentUnifiedRoomCode(?string $serviceUnifiedRoomCode, string $unifiedRoomCode): bool
    {
        return !is_null($serviceUnifiedRoomCode) && $serviceUnifiedRoomCode !== $unifiedRoomCode;
    }

    /**
     * Check if the service is not mandatory.
     *
     * @param int $autoBook
     * @return bool
     */
    protected function isNotMandatory(int $autoBook): bool
    {
        return $autoBook === 0;
    }

    /**
     * Initialize the Fees array if it doesn't exist.
     *
     * @param array $rate
     */
    protected function initializeFeesArray(array &$rate): void
    {
        if (!isset($rate['Fees'])) {
            $rate['Fees'] = [];
        }
    }

    /**
     * Calculate the rate amount based on the apply type.
     *
     * @param float|null $value The value to be calculated.
     * @param string $applyType The apply type (e.g., PER_PERSON, PER_NIGHT_PER_PERSON).
     * @param int $numberOfNights The number of nights.
     * @param int|null $numberOfPassengers The number of passengers (optional).
     * @return float The calculated rate amount.
     */
    private function calculateRateAmount(?float $value, string $applyType, int $numberOfNights, ?int $numberOfPassengers = null): float
    {
        return match ($applyType) {
            ProductApplyTypeEnum::PER_PERSON->value => round(($value / $numberOfNights), 3),
            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => round(($value * $numberOfPassengers / $numberOfNights), 3),
            default => $value,
        };
    }
}
