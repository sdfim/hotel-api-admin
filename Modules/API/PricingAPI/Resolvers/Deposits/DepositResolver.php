<?php

namespace Modules\API\PricingAPI\Resolvers\Deposits;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\Enums\ProductManipulablePriceTypeEnum;
use Modules\Enums\ProductPriceValueTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;

class DepositResolver
{
    const CACHE_TTL_MINUTES = 1;

    public static function getRateLevel(RoomResponse $roomResponse, array $depositInformation, array $query, $giataId): array
    {
        if (empty($depositInformation)) {
            return [];
        }

        $productId = Arr::get($depositInformation, '0.product_id');
        $rates = self::getRatesByProductId($productId);

        $activeDepositInformation = self::getCachedFilteredDepositInformation($depositInformation, $query, $giataId);
        $activeDepositInformationRateLevel = $activeDepositInformation['rate']->isNotEmpty()
            ? $activeDepositInformation['rate']
            : $activeDepositInformation['hotel'];

        $calculatedDeposits = [];
        foreach ($activeDepositInformationRateLevel as $depositInfo) {
            $rateId = Arr::get($depositInfo, 'rate_id');
            $level = $rateId ? 'rate' : 'hotel';
            $baseAmount = self::getBaseAmount($roomResponse, $depositInfo);
            $calculatedDeposits[] = [
                'name' => $depositInfo['name'],
                'level' => $level,
                'base_price_type' => $depositInfo['manipulable_price_type'],
                'price_value' => $depositInfo['price_value'],
                'price_value_type' => $depositInfo['price_value_type'],
                'compare' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'travel_date'), 'compare'),
                'interval' => [
                    'from' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'travel_date'), 'value_from'),
                    'to' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'travel_date'), 'value_to'),
                ],
                'base_price_amount' => $baseAmount,
                'total_deposit' => self::calculate($depositInfo, $baseAmount, self::getMultiplier($depositInfo, $query)),
            ];
        }

        return $calculatedDeposits;
    }

    public static function getHotelLevel(array $depositInformation, array $query, $giataId): array
    {
        if (empty($depositInformation)) {
            return [];
        }

        $activeDepositInformation = self::getCachedFilteredDepositInformation($depositInformation, $query, $giataId);
        $activeDepositInformationHotelLevel = $activeDepositInformation['hotel'];

        $calculatedDeposits = [];
        foreach ($activeDepositInformationHotelLevel as $depositInfo) {
            $calculatedDeposits[] = [
                'name' => Arr::get($depositInfo, 'name'),
                'level' => 'hotel',
                'base_price_type' => $depositInfo['manipulable_price_type'],
                'price_value' => $depositInfo['price_value'],
                'price_value_type' => $depositInfo['price_value_type'],
                'compare' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'travel_date'), 'compare'),
                'interval' => [
                    'from' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'travel_date'), 'value_from'),
                    'to' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'travel_date'), 'value_to'),
                ],
            ];
        }

        return $calculatedDeposits;
    }

    private static function getCachedFilteredDepositInformation(array $depositInformation, array $query, $giataId): array
    {
        $cacheKey = "filtered_deposit_information_{$giataId}";

        return cache()->remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($depositInformation, $query) {
            return self::getFilteredDepositInformation($depositInformation, $query);
        });
    }

    private static function getFilteredDepositInformation(array $depositInformation, array $query): array
    {
        $hotelLevel = self::filterPeriodDeposit(collect($depositInformation), $query);
        $rateLevel = self::filterPeriodDeposit(collect($depositInformation), $query, 'rate');

        return [
            'hotel' => $hotelLevel,
            'rate' => $rateLevel,
        ];
    }

    private static function getRatesByProductId(int $productId): array
    {
        return cache()->remember("hotel_rates_by_product_{$productId}", now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($productId) {
            $hotel = Hotel::whereHas('product', function ($query) use ($productId) {
                $query->where('id', $productId);
            })->first();

            return $hotel ? $hotel->rates->pluck('code', 'id')->toArray() : [];
        });
    }

    private static function calculate(array $depositInfo, float $basePrice, int $multiplier): float
    {
        $priceValue = (float) $depositInfo['price_value'];
        $priceValueType = $depositInfo['price_value_type'];
        $value = $priceValueType === ProductPriceValueTypeEnum::PERCENTAGE->value ? ($basePrice * $priceValue) / 100 : $priceValue;

        return $value * $multiplier;
    }

    private static function getBaseAmount(RoomResponse $roomResponse, array $depositInfo): float
    {
        return $depositInfo['manipulable_price_type'] === ProductManipulablePriceTypeEnum::TOTAL_PRICE->value ? $roomResponse->getTotalPrice() : $roomResponse->getTotalNet();
    }

    private static function getNights($from, $to): int
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

        return $from->diffInDays($to);
    }

    private static function getMultiplier(array $depositInfo, array $query): int
    {
        return match ($depositInfo['price_value_target']) {
            ProductApplyTypeEnum::PER_NIGHT->value => self::getNights($query['checkin'], $query['checkout']),
            ProductApplyTypeEnum::PER_PERSON->value => collect($query['occupancy'])->reduce(function ($accum, $item) {
                return $accum + (int) Arr::get($item, 'adults', 0) + (int) Arr::get($item, 'children', 0);
            }, 0),
            ProductApplyTypeEnum::PER_ROOM->value => count($query['occupancy']),
            default => 1,
        };
    }

    private static function filterPeriodDeposit(Collection $depositInformation, array $query, string $level = 'hotel'): Collection
    {
        $checkin = Carbon::parse($query['checkin']);
        $checkout = Carbon::parse($query['checkout']);

        // Filter intervals that overlap with $checkin-$checkout
        $filtered = $depositInformation->filter(function ($item) use ($checkin, $checkout, $level) {
            $condition = collect($item['conditions'])->firstWhere('field', 'travel_date');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));

            return (($from <= $checkout && $to >= $checkin)
                || ($condition['compare'] === '=' && ($from <= $checkout && $from >= $checkin)))
                    && (($level === 'hotel') ? $item['rate_id'] === null : $item['rate_id'] !== null);
        });

        // Add filter for booking_date to ensure the current date is within the interval
        $filtered = $filtered->filter(function ($item) {
            $condition = collect($item['conditions'])->firstWhere('field', 'booking_date');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));
            $currentDate = Carbon::now();

            return $from <= $currentDate && $to >= $currentDate;
        });

        // Retain only intervals that are not nested within other intervals
        return $filtered->reject(function ($item) use ($filtered) {
            $condition = collect($item['conditions'])->firstWhere('field', 'travel_date');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));

            return $filtered->contains(function ($existing) use ($from, $to) {
                $existingCondition = collect($existing['conditions'])->firstWhere('field', 'travel_date');
                $existingFrom = Carbon::parse($existingCondition['value_from'] ?? Carbon::now());
                $existingTo = Carbon::parse($existingCondition['value_to'] ?? Carbon::now()->addYears(1000));

                // Check if the current interval ($from, $to) is fully nested within another interval
                return ($from >= $existingFrom && $to <= $existingTo && ($from != $existingFrom || $to != $existingTo))
                    && ($existingCondition['compare'] !== '=');
            });
        });
    }
}
