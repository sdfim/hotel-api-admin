<?php

namespace Modules\API\PricingAPI\Resolvers\CancellationPolicies;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;

class CancellationPolicyResolver
{
    const CACHE_TTL_MINUTES = 1;

    public static function getRateLevel(RoomResponse $roomResponse, array $cancellationPolicies, array $query, $giataId): array
    {
        if (empty($cancellationPolicies)) {
            return [];
        }

        try {
            $st = microtime(true);
            $ratePlanCode = $roomResponse->getRatePlanCode();

            $activeCancellationPolicies = self::getCachedFilteredCancellationPolicies($cancellationPolicies, $query, $giataId);

            if (!isset($activeCancellationPolicies['rate']) && !isset($activeCancellationPolicies['hotel'])) {
                return [];
            }

            $activeCancellationPoliciesRateLevel = $activeCancellationPolicies['rate']->isNotEmpty()
                ? collect(array_merge($activeCancellationPolicies['rate']->toArray(), $activeCancellationPolicies['hotel']->toArray()))
                : $activeCancellationPolicies['hotel'];

            $filtered = $activeCancellationPoliciesRateLevel;

            $cancellationPolicies = [];
            foreach ($filtered as $cancellationInfo) {
                $rateId = Arr::get($cancellationInfo, 'rate_id');
                $level = $rateId ? 'rate' : 'hotel';
                
                if ($level === 'rate' && $ratePlanCode && $rateId !== $ratePlanCode) {
                    continue; // Skip if rate_id does not match the rate plan code
                }
                
                $cancellationPolicies[] = [
                    'description' => $cancellationInfo['name'] ?? '',
                    'type' => $cancellationInfo['name'] ?? '',
                    'level' => $level,
                    'penalty_start_date' => $cancellationInfo['start_date'] ?? null,
                    'penalty_end_date' => $cancellationInfo['expiration_date'] ?? null,
                    'nights' => $cancellationInfo['nights'] ?? null,
                    'currency' => $cancellationInfo['currency'] ?? ($query['currency'] ?? null),
                    'amount' => ($cancellationInfo['price_value_type'] ?? null) === 'fixed_value' ? $cancellationInfo['price_value'] : null,
                    'percentage' => ($cancellationInfo['price_value_type'] ?? null) === 'percentage' ? $cancellationInfo['price_value'] : null,
                ];
            }

            logger('getRateLevel _ '.($roomResponse->getRoomType() ?? 'unknown').' _ execute '.microtime(true) - $st.' seconds', [
                'giataId' => $giataId ?? 'unknown',
            ]);

            return $cancellationPolicies;
        } catch (\Exception $e) {
            logger('Error in CancellationPolicyResolver::getRateLevel: ' . $e->getMessage(), [
                'exception' => $e,
                'giataId' => $giataId ?? 'unknown',
            ]);
            return [];
        }
    }

    public static function getHotelLevel(array $cancellationPolicies, array $query, $giataId): array
    {
        if (empty($cancellationPolicies)) {
            return [];
        }

        try {
            $st = microtime(true);

            $activeCancellationPolicies = self::getCachedFilteredCancellationPolicies($cancellationPolicies, $query, $giataId);

            if (!isset($activeCancellationPolicies['hotel'])) {
                return [];
            }

            $activeCancellationPoliciesHotelLevel = $activeCancellationPolicies['hotel'];

            $cancellationPolicies = [];
            foreach ($activeCancellationPoliciesHotelLevel as $cancellationInfo) {
                $cancellationPolicies[] = [
                    'description' => $cancellationInfo['name'] ?? '',
                    'type' => $cancellationInfo['name'] ?? '',
                    'level' => 'hotel',
                    'penalty_start_date' => $cancellationInfo['start_date'] ?? null,
                    'penalty_end_date' => $cancellationInfo['expiration_date'] ?? null,
                    'nights' => $cancellationInfo['nights'] ?? null,
                    'currency' => $cancellationInfo['currency'] ?? ($query['currency'] ?? null),
                    'amount' => ($cancellationInfo['price_value_type'] ?? null) === 'fixed_value' ? $cancellationInfo['price_value'] : null,
                    'percentage' => ($cancellationInfo['price_value_type'] ?? null) === 'percentage' ? $cancellationInfo['price_value'] : null,
                ];
            }

            logger('getHotelLevel _ execute '.microtime(true) - $st.' seconds', [
                'giataId' => $giataId ?? 'unknown',
            ]);

            return $cancellationPolicies;
        } catch (\Exception $e) {
            logger('Error in CancellationPolicyResolver::getHotelLevel: ' . $e->getMessage(), [
                'exception' => $e,
                'giataId' => $giataId ?? 'unknown',
            ]);
            return [];
        }
    }

    private static function getCachedFilteredCancellationPolicies(array $cancellationPolicies, array $query, $giataId): array
    {
        $cacheKey = "filtered_cancellation_policies_{$giataId}";

        return cache()->remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($cancellationPolicies, $query) {
            return self::getFilteredCancellationPolicies($cancellationPolicies, $query);
        });
    }

    private static function getFilteredCancellationPolicies(array $cancellationPolicies, array $query): array
    {
        $hotelLevel = self::primaryFiltersCancellationPolicies(collect($cancellationPolicies), $query);
        $rateLevel = self::primaryFiltersCancellationPolicies(collect($cancellationPolicies), $query, 'rate');

        return [
            'hotel' => $hotelLevel,
            'rate' => $rateLevel,
        ];
    }

    private static function primaryFiltersCancellationPolicies(Collection $cancellationPolicies, array $query, string $level = 'hotel'): Collection
    {
        $checkin = Carbon::parse($query['checkin'] ?? now());
        $checkout = Carbon::parse($query['checkout'] ?? now()->addDays(1));

        $filtered = $cancellationPolicies->filter(function ($item) use ($level) {
            return ($level === 'hotel') ? ($item['rate_id'] ?? null) === null : ($item['rate_id'] ?? null) !== null;
        });

        $filtered = $filtered->filter(function ($item) use ($checkin, $checkout) {
            if (!isset($item['start_date']) || !isset($item['end_date'])) {
                return true;
            }

            if ($item['start_date'] === null || $item['end_date'] === null) {
                return true;
            }

            $from = Carbon::parse($item['start_date']);
            $to = Carbon::parse($item['end_date']);

            return ($from <= $checkout && $to >= $checkin);
        });

        return $filtered;
    }
} 