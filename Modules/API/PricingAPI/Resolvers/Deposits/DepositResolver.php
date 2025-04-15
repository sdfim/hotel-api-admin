<?php

namespace Modules\API\PricingAPI\Resolvers\Deposits;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;

class DepositResolver
{
    const MANIPULABLE_TOTAL_PRICE = 'total_price';

    const MANIPULABLE_NET_PRICE = 'net_price';

    const PRICE_VALUE_TYPE = 'fixed_value';

    const PRICE_PERCENTAGE_TYPE = 'percentage';

    const PRICE_VALUE_TARGET_NA = 'not_applicable';

    const PRICE_VALUE_TARGET_PER_NIGHT = 'per_night';

    const PRICE_VALUE_TARGET_PER_ROOM = 'per_room';

    const PRICE_VALUE_TARGET_PER_GUEST = 'per_guest';

    private static function calculate(array $depositInfo, float $basePrice, int $multiplier): float
    {
        $priceValue = (float) $depositInfo['price_value'];
        $priceValueType = $depositInfo['price_value_type'];
        $value = $priceValueType === self::PRICE_PERCENTAGE_TYPE ? ($basePrice * $priceValue) / 100 : $priceValue;

        return $value * $multiplier;
    }

    private static function getBaseAmount(RoomResponse $roomResponse, array $depositInfo): float
    {
        return $depositInfo['manipulable_price_type'] === self::MANIPULABLE_TOTAL_PRICE ? $roomResponse->getTotalPrice() : $roomResponse->getTotalNet();
    }

    private static function getNights($from, $to): int
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

        return $from->diffInDays($to);
    }

    private static function getMultiplier(RoomResponse $roomResponse, array $depositInfo, array $query): int
    {
        return match ($depositInfo['price_value_target']) {
            self::PRICE_VALUE_TARGET_NA => 1,
            self::PRICE_VALUE_TARGET_PER_NIGHT => self::getNights($query['checkin'], $query['checkout']),
            self::PRICE_VALUE_TARGET_PER_GUEST => collect($query['occupancy'])->reduce(function ($accum, $item) {
                return $accum + (int) Arr::get($item, 'adults', 0) + (int) Arr::get($item, 'children', 0);
            }, 0),
            self::PRICE_VALUE_TARGET_PER_ROOM => count($query['occupancy']),
        };
    }

    public static function resolve(RoomResponse $roomResponse, array $depositInformation, array $query): array
    {
        $activeDepositInformation = self::filterActiveDeposit(collect($depositInformation));

        $calculatedDeposits = [];
        foreach ($activeDepositInformation as $depositInfo) {
            $baseAmount = self::getBaseAmount($roomResponse, $depositInfo);
            $calculatedDeposits[] = [
                'name' => $depositInfo['name'],
                'base_price_type' => $depositInfo['manipulable_price_type'],
                'base_price_amount' => $baseAmount,
                'total_deposit' => self::calculate($depositInfo, $baseAmount, self::getMultiplier($roomResponse, $depositInfo, $query)),
            ];
        }

        return $calculatedDeposits;
    }

    private static function filterActiveDeposit(Collection $depositInformation): Collection
    {
        return collect($depositInformation)->filter(function ($item) {
            $from = Carbon::parse($item['start_date']);
            $to = Carbon::parse($item['expiration_date']);

            return Carbon::now()->endOfDay()->isAfter($from) && $to->isAfter(Carbon::now()->startOfDay());
        });
    }
}
