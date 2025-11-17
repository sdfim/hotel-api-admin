<?php

namespace Modules\API\PricingAPI\Resolvers\DescriptiveContent;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Throwable;

class DescriptiveContentResolver
{
    public const CACHE_TTL_MINUTES = 1;

    public static function getHotelLevel(array $descriptiveContent, array $query, $giataId): array
    {
        if (empty($descriptiveContent)) {
            return [];
        }

        try {
            $active = self::getCachedFilteredDescriptiveContent($descriptiveContent, $query, $giataId);
            if (! isset($active['hotel'])) {
                return [];
            }

            $out = [];
            foreach ($active['hotel'] as $item) {
                $out[] = self::mapItem($item, 'hotel');
            }
            foreach ($active['room'] as $item) {
                if (isset($item['descriptive_type_name']) && $item['descriptive_type_name'] === 'Rate Inclusions') {
                    $out[] = self::mapItem($item, 'room');
                }
            }
            foreach ($active['rate'] as $item) {
                if (isset($item['descriptive_type_name']) && $item['descriptive_type_name'] === 'Rate Inclusions') {
                    $out[] = self::mapItem($item, 'rate');
                }
            }

            return $out;
        } catch (Throwable $e) {
            logger('Error in DescriptiveContentResolver::getHotelLevel: '.$e->getMessage(), [
                'exception' => $e, 'giataId' => $giataId ?? 'unknown',
            ]);

            return [];
        }
    }

    public static function getRoomLevel(RoomResponse $roomResponse, array $descriptiveContent, array $query, $giataId): array
    {
        if (empty($descriptiveContent)) {
            return [];
        }

        try {
            $active = self::getCachedFilteredDescriptiveContent($descriptiveContent, $query, $giataId);

            if (empty($active['room'])) {
                return [];
            }

            $unified = $roomResponse->getUnifiedRoomCode(); // for example "UPRR" or "UA1S"

            $out = [];

            foreach ($active['room'] as $item) {
                if ($unified && (Arr::get($item, 'unified_room_code') === $unified)) {
                    $out[] = self::mapItem($item, 'room');
                }
            }

            return $out;
        } catch (Throwable $e) {
            logger('Error in DescriptiveContentResolver::getRoomLevel: '.$e->getMessage(), [
                'exception' => $e,
                'giataId' => $giataId ?? 'unknown',
            ]);

            return [];
        }
    }

    public static function getRateLevel(RoomResponse $roomResponse, array $descriptiveContent, array $query, $giataId, $unifiedRoomCode): array
    {
        if (empty($descriptiveContent)) {
            return [];
        }

        try {
            $ratePlanCode = $roomResponse->getRatePlanCode();
            $active = self::getCachedFilteredDescriptiveContent($descriptiveContent, $query, $giataId);
            if (! isset($active['rate'])) {
                return [];
            }

            $out = [];
            foreach ($active['rate'] as $item) {
                if (Arr::get($item, 'rate_id') && Arr::get($item, 'rate_id') && in_array($unifiedRoomCode, Arr::get($item, 'priority_rooms')) == $ratePlanCode) {
                    $out[] = self::mapItem($item, 'rate');
                }
            }

            return $out;
        } catch (Throwable $e) {
            logger('Error in DescriptiveContentResolver::getRateLevel: '.$e->getMessage(), [
                'exception' => $e, 'giataId' => $giataId ?? 'unknown',
            ]);

            return [];
        }
    }

    public static function getForRoomResponse(RoomResponse $roomResponse, array $descriptiveContent, array $query, $giataId): array
    {
        $hotel = self::getHotelLevel($descriptiveContent, $query, $giataId);
        $room = self::getRoomLevel($roomResponse, $descriptiveContent, $query, $giataId);
        $rate = self::getRateLevel($roomResponse, $descriptiveContent, $query, $giataId, '');

        return array_values(array_merge($hotel, $room, $rate));
    }

    public static function getRoomAndRateForRoomResponse(RoomResponse $roomResponse, array $descriptiveContent, array $query, $giataId, $unifiedRoomCode): array
    {
        $room = self::getRoomLevel($roomResponse, $descriptiveContent, $query, $giataId);
        $rate = self::getRateLevel($roomResponse, $descriptiveContent, $query, $giataId, $unifiedRoomCode);

        return array_values(array_merge($room, $rate));
    }

    public static function getRateInclusions(array $descriptiveContent, array $query, string $ratePlanCode, string $unifiedRoomCode): string
    {
        if (empty($descriptiveContent)) {
            return '';
        }
        $contentCollection = collect($descriptiveContent);
        $filteredContent = self::primaryFiltersRateInclusions($contentCollection, $query, $ratePlanCode, $unifiedRoomCode);
        $filteredContentAtHotelLevel = self::getRateInclusionsAtHotelLevel($descriptiveContent, $query);

        $rateInclusions = $filteredContent->pluck('value')->filter()->toArray();
        if ($rateInclusions == []) {
            $rateInclusions = $filteredContentAtHotelLevel."\n";
        } else {
            $rateInclusions = implode("\n", $rateInclusions)."\n".$filteredContentAtHotelLevel."\n";
        }

        return $rateInclusions;
    }

    public static function getRateInclusionsAtHotelLevel(array $descriptiveContent, array $query): string
    {
        if (empty($descriptiveContent)) {
            return '';
        }

        $contentCollection = collect($descriptiveContent);
        $filteredContent = self::primaryFiltersRateInclusions($contentCollection, $query, '', '', true);

        if ($filteredContent->isEmpty()) {
            return '';
        }

        $hotelInclusions = $filteredContent->pluck('value')->filter()->toArray();

        return implode("\n", $hotelInclusions);
    }

    private static function getCachedFilteredDescriptiveContent(array $descriptiveContent, array $query, $giataId): array
    {
        $relevantParams = [
            'checkin' => $query['checkin'] ?? 'default',
            'checkout' => $query['checkout'] ?? 'default',
            'radius' => $query['radius'] ?? 'default',
            'rating' => $query['rating'] ?? 'default',
            'supplier' => $query['supplier'] ?? 'default',
            'occupancy' => json_encode($query['occupancy'] ?? []),
        ];

        $paramsHash = md5(json_encode($relevantParams));
        $cacheKey = "filtered_descriptive_content_{$giataId}_$paramsHash";

        return cache()->remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => self::getFilteredDescriptiveContent($descriptiveContent, $query)
        );
    }

    private static function getFilteredDescriptiveContent(array $descriptiveContent, array $query): array
    {
        $collection = collect($descriptiveContent);

        return [
            'hotel' => self::primaryFiltersDescriptiveContent($collection, $query, 'hotel'),
            'room' => self::primaryFiltersDescriptiveContent($collection, $query, 'room'),
            'rate' => self::primaryFiltersDescriptiveContent($collection, $query, 'rate'),
        ];
    }

    private static function primaryFiltersDescriptiveContent(Collection $descriptiveContent, array $query, string $level): Collection
    {
        $checkin = Carbon::parse($query['checkin'] ?? now());
        $checkout = Carbon::parse($query['checkout'] ?? now()->addDays());

        $filtered = $descriptiveContent->filter(function ($item) use ($level) {
            $hasRate = ! is_null(Arr::get($item, 'rate_id'));
            $hasRoom = self::hasRoomIdentifiers($item);

            return match ($level) {
                'rate' => $hasRate,
                'room' => ! $hasRate && $hasRoom,
                'hotel' => ! $hasRate && ! $hasRoom,
                default => false,
            };
        });

        return $filtered->filter(function ($item) use ($checkin, $checkout) {
            $from = Arr::get($item, 'start_date');
            $to = Arr::get($item, 'end_date');

            if (! $from || ! $to) {
                return true;
            }

            $from = Carbon::parse($from);
            $to = Carbon::parse($to);

            return $from <= $checkout && $to >= $checkin;
        })->values();
    }

    private static function primaryFiltersRateInclusions(
        Collection $descriptiveContent,
        array $query,
        string $ratePlanCode,
        string $unifiedRoomCode,
        bool $isHotelInclusions = false
    ): Collection {
        $checkin = Carbon::parse($query['checkin'] ?? now());
        $checkout = Carbon::parse($query['checkout'] ?? now()->addDays());

        $filtered = $descriptiveContent->filter(function ($item) use ($ratePlanCode, $unifiedRoomCode, $isHotelInclusions) {
            $isRateInclusions = (Arr::get($item, 'descriptive_type_name', '') === 'Rate Inclusions');

            if ($isHotelInclusions) {
                return $isRateInclusions && is_null(Arr::get($item, 'rate_id')) && is_null(Arr::get($item, 'room_id'));
            }

            $matchesRate = (Arr::get($item, 'rate_id', '') === $ratePlanCode);
            $matchesRoom = (Arr::get($item, 'unified_room_code', '') === $unifiedRoomCode);

            return $matchesRate || $matchesRoom;
        });

        return $filtered->filter(function ($item) use ($checkin, $checkout) {
            $from = Arr::get($item, 'start_date');
            $to = Arr::get($item, 'end_date');

            if (! $from || ! $to) {
                return true;
            }

            $from = Carbon::parse($from);
            $to = Carbon::parse($to);

            return $from <= $checkout && $to >= $checkin;
        })->values();
    }

    private static function hasRoomIdentifiers(array $item): bool
    {
        return ! is_null(Arr::get($item, 'unified_room_code'))
            || ! is_null(Arr::get($item, 'room_id'))
            || ! is_null(Arr::get($item, 'supplier_room_id'));
    }

    private static function mapItem(array $item, string $level): array
    {
        return [
            'level' => $level,
            'content_type' => $item['descriptive_type'] ?? '',
            'content_value' => $item['value'] ?? '',
            'interval' => [
                'from' => $item['start_date'] ?? null,
                'to' => $item['end_date'] ?? null,
            ],
            'type' => [
                'name' => $item['descriptive_type_name'] ?? '',
                'type' => $item['descriptive_type'] ?? '',
                'location' => $item['descriptive_type_location'] ?? '',
                'description' => $item['descriptive_type_description'] ?? '',
            ],
            'rate_id' => $item['rate_id'] ?? null,
            'room_id' => $item['room_id'] ?? null,
            'unified_room_code' => $item['unified_room_code'] ?? null,
            'supplier_room_id' => $item['supplier_room_id'] ?? null,
        ];
    }
}
