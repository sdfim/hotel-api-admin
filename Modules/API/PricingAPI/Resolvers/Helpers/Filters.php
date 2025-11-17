<?php

namespace Modules\API\PricingAPI\Resolvers\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class Filters
{
    public const AGE_ADULTS = 30;

    /**
     * Filter repo services.
     *
     * @return array Filtered list of services (array of service arrays)
     */
    public static function filterRepoServices(array $services, Carbon $checkin, Carbon $checkout, int $numberOfNights, array $occupancy): array
    {
        return array_values(array_filter($services, function ($service) use ($checkin, $checkout, $numberOfNights, $occupancy) {
            // 1. Date overlap check (stay period overlaps service period)
            $startDate = Arr::get($service, 'start_date');
            $endDate = Arr::get($service, 'end_date');

            if ($startDate || $endDate) {
                if (! $startDate) {
                    $startDate = Carbon::now()->format('Y-m-d');
                }
                if (! $endDate) {
                    $endDate = Carbon::parse($checkout)->addDay()->format('Y-m-d');
                }

                $startDate = Carbon::parse($startDate);
                $endDate = Carbon::parse($endDate);

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
            $ageToRaw = Arr::get($service, 'age_to', null);
            $ageFrom = is_null($ageFromRaw) ? null : (int) $ageFromRaw;
            $ageTo = is_null($ageToRaw) ? null : (int) $ageToRaw;

            if (! is_null($ageFrom) && ! is_null($ageTo) && $ageTo > $ageFrom && $ageTo < 18) {
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
     * Calculate the number of persons that meet the age restrictions for a specific service.
     *
     * @param  array  $service
     */
    public static function getEligiblePersonsCount(array $fee, array $occupancy): int
    {
        $ageFrom = Arr::get($fee, 'age_from') ?? 0;
        $ageTo = Arr::get($fee, 'age_to') ?? 99;

        // Count only persons that meet age restrictions
        $eligibleCount = 0;
        foreach ($occupancy as $occ) {
            // Count adults (always eligible if no age restrictions or if they meet age range)
            if ($ageFrom <= static::AGE_ADULTS && static::AGE_ADULTS <= $ageTo) {
                $eligibleCount += ($occ['adults'] ?? 0);
            }

            // Count children that meet age restrictions
            if (isset($occ['children_ages'])) {
                foreach ($occ['children_ages'] as $childAge) {
                    if ($childAge >= $ageFrom && $childAge <= $ageTo) {
                        $eligibleCount++;
                    }
                }
            }
        }

        return $eligibleCount;
    }
}
