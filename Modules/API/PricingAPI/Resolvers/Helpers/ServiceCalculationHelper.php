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
