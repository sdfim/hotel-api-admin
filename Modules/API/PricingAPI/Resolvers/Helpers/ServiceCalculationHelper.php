<?php

namespace Modules\API\PricingAPI\Resolvers\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Enums\ProductApplyTypeEnum;

class ServiceCalculationHelper
{
    /**
     * Calculate the number of nights a service is available within the search period.
     */
    public static function calculateServiceAvailableNights(array $service, Carbon $checkin, Carbon $checkout, int $totalNumberOfNights): int
    {
        $startDate = Arr::get($service, 'start_date');
        $endDate = Arr::get($service, 'end_date');

        if (! $startDate || ! $endDate) {
            return $totalNumberOfNights;
        }

        $serviceStartDate = Carbon::parse($startDate);
        $serviceEndDate = Carbon::parse($endDate);

        $effectiveStartDate = $checkin->max($serviceStartDate);
        $effectiveEndDate = $checkout->min($serviceEndDate->copy()->addDay());

        if ($effectiveStartDate >= $effectiveEndDate) {
            return 0;
        }

        return $effectiveStartDate->diffInDays($effectiveEndDate);
    }

    /**
     * Get the multiplier for a service based on its apply_type.
     *
     * @param  string  $type  'Tax' or 'Fee' to determine calculation logic
     */
    public static function getServiceMultiplier(array $service, int $numberOfNights, array $occupancy = [], string $type = 'Fee'): int
    {
        $applyType = $service['apply_type'] ?? ProductApplyTypeEnum::PER_ROOM->value;

        // For per_person and per_night_per_person, we need to count only people that meet age restrictions
        if (in_array($applyType, [ProductApplyTypeEnum::PER_PERSON->value, ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value])) {
            $eligiblePersons = Filters::getEligiblePersonsCount($service, $occupancy);

            return match ($applyType) {
                ProductApplyTypeEnum::PER_PERSON->value => $eligiblePersons,
                ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => $numberOfNights * $eligiblePersons,
                default => 1,
            };
        }

        return match ($applyType) {
            ProductApplyTypeEnum::PER_NIGHT->value => $numberOfNights,
            ProductApplyTypeEnum::PER_ROOM->value => $type === 'Tax' ? $numberOfNights : 1,
            default => 1,
        };
    }

    /**
     * Filter taxes that apply to a specific night date.
     *
     * @param  array  $taxes  The taxes to filter.
     * @param  Carbon  $nightDate  The specific night date.
     * @return array The filtered taxes.
     */
    public static function filterTaxesForDate(array $taxes, Carbon $nightDate): array
    {
        $filteredTaxes = [];

        foreach ($taxes as $tax) {
            if (isset($tax['start_date']) && isset($tax['end_date'])) {
                $startDate = Carbon::parse($tax['start_date']);
                $endDate = Carbon::parse($tax['end_date']);

                if ($nightDate->between($startDate, $endDate)) {
                    $filteredTaxes[] = $tax;
                }
            } else {
                $filteredTaxes[] = $tax;
            }
        }

        return $filteredTaxes;
    }
}
