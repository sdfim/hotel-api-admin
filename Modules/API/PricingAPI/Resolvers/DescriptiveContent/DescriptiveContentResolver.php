<?php

namespace Modules\API\PricingAPI\Resolvers\DescriptiveContent;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class DescriptiveContentResolver
{
    const CACHE_TTL_MINUTES = 1;

    public static function getRateLevel(RoomResponse $roomResponse, array $descriptiveContent, array $query, $giataId): array
    {
        if (empty($descriptiveContent)) {
            return [];
        }

        try {
            $st = microtime(true);
            $ratePlanCode = $roomResponse->getRatePlanCode();

            $activeDescriptiveContent = self::getCachedFilteredDescriptiveContent($descriptiveContent, $query, $giataId);

            if (!isset($activeDescriptiveContent['rate']) || !isset($activeDescriptiveContent['hotel'])) {
                return [];
            }

            $activeDescriptiveContentRateLevel = $activeDescriptiveContent['rate']->isNotEmpty()
                ? collect(array_merge($activeDescriptiveContent['rate']->toArray(), $activeDescriptiveContent['hotel']->toArray()))
                : $activeDescriptiveContent['hotel'];

            $results = [];
            foreach ($activeDescriptiveContentRateLevel as $descriptiveInfo) {
                $rateId = Arr::get($descriptiveInfo, 'rate_id');
                if ($rateId && $rateId == $ratePlanCode) {
                    $results[] = [
                        'level' => 'rate',
                        'content_type' => $descriptiveInfo['descriptive_type'] ?? '',
                        'content_value' => $descriptiveInfo['value'] ?? '',
                        'interval' => [
                            'from' => $descriptiveInfo['start_date'] ?? null,
                            'to' => $descriptiveInfo['end_date'] ?? null,
                        ],
                        'type' => [
                            'name' => $descriptiveInfo['descriptive_type_name'] ?? '',
                            'type' => $descriptiveInfo['descriptive_type'] ?? '',
                            'location' => $descriptiveInfo['descriptive_type_location'] ?? '',
                            'description' => $descriptiveInfo['descriptive_type_description'] ?? '',
                        ]
                    ];
                }
            }
            return $results;
        } catch (\Exception $e) {
            logger('Error in DescriptiveContentResolver::getRateLevel: ' . $e->getMessage(), [
                'exception' => $e,
                'giataId' => $giataId ?? 'unknown',
            ]);
            return [];
        }
    }

    public static function getHotelLevel(array $descriptiveContent, array $query, $giataId): array
    {
        if (empty($descriptiveContent)) {
            return [];
        }

        try {
            $st = microtime(true);

            $activeDescriptiveContent = self::getCachedFilteredDescriptiveContent($descriptiveContent, $query, $giataId);

            if (!isset($activeDescriptiveContent['hotel'])) {
                return [];
            }

            $activeDescriptiveContentHotelLevel = $activeDescriptiveContent['hotel'];

            $descriptiveContents = [];
            foreach ($activeDescriptiveContentHotelLevel as $descriptiveInfo) {
                $descriptiveContents[] = [
                    'level' => 'hotel',
                    'content_type' => $descriptiveInfo['descriptive_type'] ?? '',
                    'content_value' => $descriptiveInfo['value'] ?? '',
                    'interval' => [
                        'from' => $descriptiveInfo['start_date'] ?? null,
                        'to' => $descriptiveInfo['end_date'] ?? null,
                    ],
                    'type' => [
                        'name' => $descriptiveInfo['descriptive_type_name'] ?? '',
                        'type' => $descriptiveInfo['descriptive_type'] ?? '',
                        'location' => $descriptiveInfo['descriptive_type_location'] ?? '',
                        'description' => $descriptiveInfo['descriptive_type_description'] ?? '',
                    ]
                ];
            }

            logger('getHotelLevel _ execute '.microtime(true) - $st.' seconds', [
                'giataId' => $giataId ?? 'unknown',
            ]);

            return $descriptiveContents;
        } catch (\Exception $e) {
            logger('Error in DescriptiveContentResolver::getHotelLevel: ' . $e->getMessage(), [
                'exception' => $e,
                'giataId' => $giataId ?? 'unknown',
            ]);
            return [];
        }
    }

    private static function getCachedFilteredDescriptiveContent(array $descriptiveContent, array $query, $giataId): array
    {
        $cacheKey = "filtered_descriptive_content_{$giataId}";

        return cache()->remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($descriptiveContent, $query) {
            return self::getFilteredDescriptiveContent($descriptiveContent, $query);
        });
    }

    private static function getFilteredDescriptiveContent(array $descriptiveContent, array $query): array
    {
        $hotelLevel = self::primaryFiltersDescriptiveContent(collect($descriptiveContent), $query);
        $rateLevel = self::primaryFiltersDescriptiveContent(collect($descriptiveContent), $query, 'rate');

        return [
            'hotel' => $hotelLevel,
            'rate' => $rateLevel,
        ];
    }

    private static function primaryFiltersDescriptiveContent(Collection $descriptiveContent, array $query, string $level = 'hotel'): Collection
    {
        $checkin = Carbon::parse($query['checkin'] ?? now());
        $checkout = Carbon::parse($query['checkout'] ?? now()->addDays(1));

        $filtered = $descriptiveContent->filter(function ($item) use ($level) {
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

    private static function primaryFiltersRateInclusions(Collection $descriptiveContent, array $query, string $ratePlanCode, bool $isHotelInclusions = false): Collection
    {
        $checkin = Carbon::parse($query['checkin'] ?? now());
        $checkout = Carbon::parse($query['checkout'] ?? now()->addDays(1));

        $filtered = $descriptiveContent->filter(function ($item) use ($ratePlanCode, $isHotelInclusions) {
            $isRateInclusions = ($item['descriptive_type_name'] ?? '') === 'Rate Inclusions';

            if ($isHotelInclusions) {
                return $isRateInclusions && ($item['rate_id'] ?? null) === null;
            }

            $hasRateId = ($item['rate_id'] ?? null) !== null;
            $matchesRateCode = ($item['rate_id'] ?? '') === $ratePlanCode;

            return $hasRateId && $matchesRateCode && $isRateInclusions;
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

    public static function getRateInclusions(array $descriptiveContent, array $query, string $ratePlanCode): string
    {
        if (empty($descriptiveContent)) {
            return '';
        }
        $contentCollection = collect($descriptiveContent);
        $filteredContent = self::primaryFiltersRateInclusions($contentCollection, $query, $ratePlanCode, false);
        $filteredContentAtHotelLevel = self::getRateInclusionsAtHotelLevel($descriptiveContent, $query);

        $rateInclusions = $filteredContent->pluck('value')->filter()->toArray();
        if ($rateInclusions == []) {
            $rateInclusions = $filteredContentAtHotelLevel . "\n";
        } else {
            $rateInclusions = implode("\n", $rateInclusions) . "\n" . $filteredContentAtHotelLevel . "\n";
        }

        return $rateInclusions;
    }

    public static function getRateInclusionsAtHotelLevel(array $descriptiveContent, array $query): string
    {
        if (empty($descriptiveContent)) {
            return '';
        }

        $contentCollection = collect($descriptiveContent);
        $filteredContent = self::primaryFiltersRateInclusions($contentCollection, $query, '', true);

        if ($filteredContent->isEmpty()) {
            return '';
        }

        $hotelInclusions = $filteredContent->pluck('value')->filter()->toArray();

        return implode("\n", $hotelInclusions);
    }
}
