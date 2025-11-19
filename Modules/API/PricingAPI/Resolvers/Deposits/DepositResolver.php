<?php

namespace Modules\API\PricingAPI\Resolvers\Deposits;

use App\Models\Channel;
use App\Models\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\Enums\ProductManipulablePriceTypeEnum;
use Modules\Enums\ProductPriceValueTypeEnum;

class DepositResolver
{
    const CACHE_TTL_MINUTES = 1;

    public static function get(
        RoomResponse $roomResponse,
        array $depositInformation,
        array $query,
        int|string $giataId,
        float|int $rating,
        array $roomCodes,
        string $supplierName,
    ): array {
        if (empty($depositInformation)) {
            return [];
        }
        $roomResponseRoomType = $roomResponse->getUnifiedRoomCode();
        $roomResponseRoomName = $roomResponse->getSupplierRoomName();
        $depositInformationRateCode = $roomResponse->getRatePlanCode();
        $totalPrice = $roomResponse->getTotalPrice();

        $activeDepositInformation = self::getCachedFilteredDepositInformation($depositInformation, $query, $giataId, $rating, $supplierName);

        $filtered = $activeDepositInformation['rate']->filter(function ($item) use ($depositInformationRateCode) {
            $rateId = $item['rate_id'] ?? null;

            return $rateId === $depositInformationRateCode || $rateId === null;
        });

        $hotelLevelDeposits = $activeDepositInformation['hotel'];
        $filtered = $filtered->merge($hotelLevelDeposits);

        if ($filtered->isEmpty()) {
            return [];
        }

        // Filter by total price
        $filtered = $filtered->filter(function ($item) use ($totalPrice) {
            $condition = collect($item['conditions'])->firstWhere('field', 'total_price');
            $conditionValueFrom = $condition['value_from'] ?? null;
            $conditionValueTo = $condition['value_to'] ?? null;
            $compare = $condition['compare'] ?? null;

            return match ($compare) {
                '=' => $totalPrice === (float) $conditionValueFrom,
                '!=' => $totalPrice !== (float) $conditionValueFrom,
                '<' => $totalPrice < (float) $conditionValueTo,
                '>' => $totalPrice > (float) $conditionValueFrom,
                'between' => $totalPrice >= (float) $conditionValueFrom && $totalPrice <= (float) $conditionValueTo,
                default => true,
            };
        });

        // Filter by room name
        $filtered = $filtered->filter(function ($item) use ($roomResponseRoomName) {
            $condition = collect($item['conditions'])->firstWhere('field', 'room_name');
            $conditionValue = $condition['value_from'] ?? null;
            $compare = $condition['compare'] ?? null;

            $values = is_string($conditionValue) ? explode(';', $conditionValue) : [];

            return match ($compare) {
                '=' => $roomResponseRoomName === $conditionValue,
                '!=' => $roomResponseRoomName !== $conditionValue,
                'in' => in_array($roomResponseRoomName, $values),
                '!in' => ! in_array($roomResponseRoomName, $values),
                default => true,
            };
        });

        // Filter by room type
        $filtered = $filtered->filter(function ($item) use ($roomResponseRoomType, $roomCodes, $supplierName, $giataId) {
            $condition = collect($item['conditions'])->firstWhere('field', 'room_type');
            $conditionValue = $condition['value_from'] ?? null;
            $values = $condition['value'] ?? [];
            $compare = $condition['compare'] ?? null;

            $compareValues = [];
            foreach ($values as $value) {
                if (isset($roomCodes[$supplierName][$giataId][$value])) {
                    $compareValues[] = $roomCodes[$supplierName][$giataId][$value];
                }
            }

            return match ($compare) {
                '=' => $roomResponseRoomType === $conditionValue,
                '!=' => $roomResponseRoomType !== $conditionValue,
                'in' => in_array($roomResponseRoomType, $compareValues),
                '!in' => ! in_array($roomResponseRoomType, $compareValues),
                default => true,
            };
        });

        // Filter by rate code
        $deposits = [];
        foreach ($filtered as $depositInfo) {
            $rateId = Arr::get($depositInfo, 'rate_id');
            $level = $rateId ? 'rate' : 'hotel';
            $baseAmount = self::getBaseAmount($roomResponse, $depositInfo);
            $initialPaymentDueType = Arr::get($depositInfo, 'initial_payment_due_type');
            $calculatedDeposit = [
                'name' => $depositInfo['name'],
                'level' => $level,
                'base_price_type' => $depositInfo['manipulable_price_type'],
                'price_value' => $depositInfo['price_value'],
                'price_value_type' => $depositInfo['price_value_type'],
                'price_value_target' => $depositInfo['price_value_target'],
                'base_price_amount' => $baseAmount,
                'total_deposit' => self::calculate(
                    $depositInfo,
                    $baseAmount,
                    self::getMultiplier($depositInfo, $query),
                    $query
                ),
            ];

            $calculatedDeposit['initial_payment_due_type'] = $initialPaymentDueType;
            if ($initialPaymentDueType) {
                $calculatedDeposit['initial_payment_due'] = [
                    'type' => $initialPaymentDueType,
                    'calculated_due_date' => self::calculateDueDate($initialPaymentDueType, $depositInfo, $query, 'initial'),
                ];
                switch ($initialPaymentDueType) {
                    case 'days_after_booking':
                        $calculatedDeposit['initial_payment_due']['days_after_booking'] = Arr::get($depositInfo, 'days_after_booking_initial_payment_due');
                        break;
                    case 'days_before_arrival':
                        $calculatedDeposit['initial_payment_due']['days_before_arrival'] = Arr::get($depositInfo, 'days_before_arrival_initial_payment_due');
                        break;
                    case 'date':
                        $calculatedDeposit['initial_payment_due']['date'] = Arr::get($depositInfo, 'date_initial_payment_due');
                        break;
                    default:
                        $calculatedDeposit['initial_payment_due']['raw_value'] = Arr::get($depositInfo, 'initial_payment_due_value');
                        break;
                }
            } else {
                $calculatedDeposit['initial_payment_due'] = [
                    'type' => null,
                    'calculated_due_date' => null,
                    'debug_info' => [
                        'has_initial_payment_due_type' => isset($depositInfo['initial_payment_due_type']),
                        'initial_payment_due_type_value' => $depositInfo['initial_payment_due_type'] ?? 'not_set',
                        'available_fields' => array_keys(array_filter($depositInfo, function ($key) {
                            return str_contains($key, 'initial_payment') || str_contains($key, 'due');
                        }, ARRAY_FILTER_USE_KEY)),
                    ],
                ];
            }

            $balancePaymentDueType = Arr::get($depositInfo, 'balance_payment_due_type');
            $calculatedDeposit['balance_payment_due_type'] = $balancePaymentDueType;
            if ($balancePaymentDueType) {
                $calculatedDeposit['balance_payment_due'] = [
                    'type' => $balancePaymentDueType,
                    'calculated_due_date' => self::calculateDueDate($balancePaymentDueType, $depositInfo, $query, 'balance'),
                ];
                switch ($balancePaymentDueType) {
                    case 'days_after_booking':
                        $calculatedDeposit['balance_payment_due']['days_after_booking'] = Arr::get($depositInfo, 'days_after_booking_balance_payment_due');
                        break;
                    case 'days_before_arrival':
                        $calculatedDeposit['balance_payment_due']['days_before_arrival'] = Arr::get($depositInfo, 'days_before_arrival_balance_payment_due');
                        break;
                    case 'date':
                        $calculatedDeposit['balance_payment_due']['date'] = Arr::get($depositInfo, 'date_balance_payment_due');
                        break;
                    default:
                        $calculatedDeposit['balance_payment_due']['raw_value'] = Arr::get($depositInfo, 'balance_payment_due_value');
                        break;
                }
            } else {
                $calculatedDeposit['balance_payment_due'] = [
                    'type' => null,
                    'calculated_due_date' => null,
                    'debug_info' => [
                        'has_balance_payment_due_type' => isset($depositInfo['balance_payment_due_type']),
                        'balance_payment_due_type_value' => $depositInfo['balance_payment_due_type'] ?? 'not_set',
                        'available_fields' => array_keys(array_filter($depositInfo, function ($key) {
                            return str_contains($key, 'balance_payment') || str_contains($key, 'due');
                        }, ARRAY_FILTER_USE_KEY)),
                    ],
                ];
            }

            $deposits[] = $calculatedDeposit;
        }

        $deposits = self::processBalancePayment($deposits, $totalPrice);

        return $deposits;
    }

    /**
     * Processes the deposit array to standardize the structure and calculate the final balance.
     * * 1. Tags existing entries as 'initial_payment' and sums up the total initial payment.
     * 2. Finds the maximum (latest) balance payment due date among all rules.
     * 3. Creates the final 'balance_payment' block with the remaining amount.
     * 4. Promotes the calculated due date to a top-level 'due_date' field for all entries.
     * 5. Sorts all deposit blocks by their 'due_date'.
     *
     * @param  array  $deposits  The initial array of calculated deposits.
     * @param  float  $totalPrice  The total room price.
     * @return array The processed and sorted array of deposit blocks.
     */
    private static function processBalancePayment(array $deposits, float $totalPrice): array
    {
        if (empty($deposits)) {
            return [];
        }

        $totalInitialPayment = 0.0;
        $maxBalanceDate = null;
        $maxBalanceDueInfo = [];

        // 1. Tag existing deposits, sum up amounts, and find the latest balance due date.
        foreach ($deposits as &$deposit) {
            // Tag as initial payment
            $deposit['type'] = 'initial_payment';

            // Sum initial payments
            $amount = (float) ($deposit['total_deposit'] ?? 0);
            $totalInitialPayment += $amount;

            // Find the latest balance payment due date
            $balanceDate = $deposit['balance_payment_due']['calculated_due_date'] ?? null;

            if ($balanceDate) {
                if ($maxBalanceDate === null || $balanceDate > $maxBalanceDate) {
                    $maxBalanceDate = $balanceDate;
                    $maxBalanceDueInfo = $deposit['balance_payment_due'];
                }
            }

            // 2. Promote calculated_due_date to the top-level 'due_date'
            // For initial payments, the relevant date is initial_payment_due
            $deposit['due_date'] = $deposit['initial_payment_due']['calculated_due_date'] ?? null;
        }
        unset($deposit); // Unset the reference

        // 3. Calculate the remaining balance
        $remainingBalance = round($totalPrice - $totalInitialPayment, 2);

        // 4. If the remainder is positive, create and add the balance block
        if ($remainingBalance > 0) {
            $paymentDue = [
                'type' => $maxBalanceDueInfo['type'] ?? null,
                'calculated_due_date' => $maxBalanceDate,
            ];

            if ($maxBalanceDueInfo) {
                $paymentDue = array_merge($maxBalanceDueInfo, $paymentDue);
            }

            $balanceBlock = [
                'name' => 'Remaining Balance',
                'type' => 'balance_payment',
                'level' => 'calculated_balance',
                'total_deposit' => $remainingBalance,
                'payment_due' => $paymentDue,

                // Promote calculated_due_date to the top-level 'due_date'
                'due_date' => $maxBalanceDate,

                // Auxiliary fields
                'base_price_amount' => $totalPrice,
                'price_value_target' => 'remaining_amount',
            ];

            $deposits[] = $balanceBlock;
        }

        // 5. Sort all blocks by 'due_date'
        // We use a stable sort mechanism here, comparing dates (which are strings)
        usort($deposits, function ($a, $b) {
            $dateA = $a['due_date'] ?? '9999-12-31'; // Put items without date at the end
            $dateB = $b['due_date'] ?? '9999-12-31';

            // Custom sort logic: nulls (9999-12-31) go last, otherwise standard string comparison
            if ($dateA === $dateB) {
                return 0;
            }

            return ($dateA < $dateB) ? -1 : 1;
        });

        return $deposits;
    }

    public static function getHotelLevel(array $depositInformation, array $query, $giataId, $rating = null): array
    {
        if (empty($depositInformation)) {
            return [];
        }

        $activeDepositInformation = self::getCachedFilteredDepositInformation($depositInformation, $query, $giataId, $rating);
        $activeDepositInformationHotelLevel = $activeDepositInformation['hotel'];

        $calculatedDeposits = [];
        foreach ($activeDepositInformationHotelLevel as $depositInfo) {
            $initialPaymentDueType = Arr::get($depositInfo, 'initial_payment_due_type', null);
            $calculatedDeposit = [
                'name' => Arr::get($depositInfo, 'name'),
                'level' => 'hotel',
                'base_price_type' => $depositInfo['manipulable_price_type'],
                'price_value' => $depositInfo['price_value'],
                'price_value_type' => $depositInfo['price_value_type'],
                'compare' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'date_of_stay'), 'compare'),
                'interval' => [
                    'from' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'date_of_stay'), 'value_from'),
                    'to' => Arr::get(collect($depositInfo['conditions'])->firstWhere('field', 'date_of_stay'), 'value_to'),
                ],
            ];
            if ($initialPaymentDueType) {
                $calculatedDeposit['initial_payment_due']['type'] = $initialPaymentDueType;
                $initialPaymentDueType === 'day'
                    ? $calculatedDeposit['initial_payment_due']['days'] = Arr::get($depositInfo, 'days_initial_payment_due')
                    : $calculatedDeposit['initial_payment_due']['date'] = Arr::get($depositInfo, 'date_initial_payment_due');
            }

            $calculatedDeposits[] = $calculatedDeposit;
        }

        return $calculatedDeposits;
    }

    private static function getCachedFilteredDepositInformation(array $depositInformation, array $query, $giataId, $rating, $supplierName): array
    {
        $cacheKey = "filtered_deposit_information_{$giataId}";

        return cache()->remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($depositInformation, $query, $rating, $supplierName) {
            return self::getFilteredDepositInformation($depositInformation, $query, $rating, $supplierName);
        });
    }

    private static function getFilteredDepositInformation(array $depositInformation, array $query, $rating, $supplierName): array
    {
        $rateLevel = self::primaryFiltersDeposit(collect($depositInformation), $query, $rating, $supplierName, 'rate');
        $hotelLevel = self::primaryFiltersDeposit(collect($depositInformation), $query, $rating, $supplierName);

        return [
            'hotel' => $hotelLevel,
            'rate' => $rateLevel,
        ];
    }

    private static function calculate(array $depositInfo, float $basePrice, int $multiplier): float
    {
        $priceValue = (float) $depositInfo['price_value'];
        $priceValueType = $depositInfo['price_value_type'];
        $value = $priceValueType === ProductPriceValueTypeEnum::PERCENTAGE->value ? ($basePrice * $priceValue) / 100 : $priceValue * $multiplier;

        return $value;
    }

    private static function getBaseAmount(RoomResponse $roomResponse, array $depositInfo): float
    {
        return $depositInfo['manipulable_price_type'] === ProductManipulablePriceTypeEnum::TOTAL_PRICE->value
            ? $roomResponse->getTotalPrice()
            : $roomResponse->getTotalNet();
    }

    private static function getNights($from, $to): int
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

        return $from->diffInDays($to);
    }

    private static function getMultiplier(array $depositInfo, array $query): int
    {
        $nights = self::getNights($query['checkin'], $query['checkout']);
        $totalPersons = collect($query['occupancy'])->reduce(function ($accum, $item) {
            return $accum + (int) Arr::get($item, 'adults', 0) + (int) Arr::get($item, 'children', 0);
        }, 0);

        return match ($depositInfo['price_value_target']) {
            ProductApplyTypeEnum::PER_NIGHT->value => $nights,
            ProductApplyTypeEnum::PER_PERSON->value => $totalPersons,
            ProductApplyTypeEnum::PER_ROOM->value => count($query['occupancy']),
            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => $nights * $totalPersons,
            default => 1,
        };
    }

    private static function calculateDueDate(string $paymentDueType, array $depositInfo, array $query, string $paymentLevel = 'initial'): ?string
    {
        // Determine the field suffix based on the payment level ('_initial_payment_due' or '_balance_payment_due')
        $suffix = $paymentLevel === 'initial' ? '_initial_payment_due' : '_balance_payment_due';

        switch ($paymentDueType) {
            case 'days_after_booking':
                $field = 'days_after_booking'.$suffix;
                $days = Arr::get($depositInfo, $field, 0);
                $bookingDate = Carbon::now();

                return $bookingDate->copy()->addDays($days)->format('Y-m-d');

            case 'days_before_arrival':
                $field = 'days_before_arrival'.$suffix;
                $days = Arr::get($depositInfo, $field, 0);
                $checkinDate = Carbon::parse($query['checkin']);

                return $checkinDate->copy()->subDays($days)->format('Y-m-d');

            case 'date':
                $field = 'date'.$suffix;

                return Arr::get($depositInfo, $field);

            default:
                return null;
        }
    }

    private static function primaryFiltersDeposit(Collection $depositInformation, array $query, $rating, $supplierName, string $level = 'hotel'): Collection
    {
        $checkin = Carbon::parse($query['checkin']);
        $checkout = Carbon::parse($query['checkout']);

        // Filter by level (hotel or rate)
        $filtered = $depositInformation->filter(function ($item) use ($level) {
            return ($level === 'hotel') ? ($item['rate_id'] ?? null) === null : ($item['rate_id'] ?? null) !== null;
        });

        // Filter by rating
        $filtered = $filtered->filter(function ($item) use ($rating) {
            $condition = collect($item['conditions'])->firstWhere('field', 'rating');
            $conditionRatingFrom = $condition['value_from'] ?? null;
            $conditionRatingTo = $condition['value_to'] ?? null;
            $compare = $condition['compare'] ?? null;

            $conditionRatingFrom = floatval($conditionRatingFrom);

            return match ($compare) {
                '=' => $rating === $conditionRatingFrom,
                '!=' => $rating !== $conditionRatingFrom,
                '<' => $rating < $conditionRatingTo,
                '>' => $rating > $conditionRatingFrom,
                'between' => $rating >= $conditionRatingFrom && $rating <= $conditionRatingTo,
                default => true,
            };
        });

        // Filter intervals that overlap with $checkin-$checkout
        $filtered = $filtered->filter(function ($item) use ($checkin, $checkout) {
            $condition = collect($item['conditions'])->firstWhere('field', 'date_of_stay');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));

            return ($from <= $checkout && $to >= $checkin)
                || ($condition['compare'] === '=' && ($from <= $checkout && $from >= $checkin));
        });

        // Filter for travel_date to ensure the travel date is after $checkin
        $filtered = $filtered->filter(function ($item) use ($checkin) {
            $condition = collect($item['conditions'])->firstWhere('field', 'travel_date');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));

            return $from <= $checkin && $to >= $checkin;
        });

        // Filter for booking_date to ensure the current date is within the interval
        $filtered = $filtered->filter(function ($item) {
            $condition = collect($item['conditions'])->firstWhere('field', 'booking_date');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));
            $currentDate = Carbon::now();

            return $from <= $currentDate && $to >= $currentDate;
        });

        // Filter for date_of_stay to ensure the stay period matches the specified interval
        $filtered = $filtered->filter(function ($item) use ($checkin, $checkout) {
            $condition = collect($item['conditions'])->firstWhere('field', 'date_of_stay');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));

            return $from <= $checkout && $to >= $checkin;
        });

        // Filter for days_until_departure to ensure the days until departure match the specified interval
        $filtered = $filtered->filter(function ($item) use ($checkin) {
            $condition = collect($item['conditions'])->firstWhere('field', 'days_until_departure');
            $from = (int) ($condition['value_from'] ?? 0);
            $to = (int) ($condition['value_to'] ?? PHP_INT_MAX);

            $daysUntilDeparture = Carbon::now()->diffInDays($checkin);

            return $daysUntilDeparture >= $from && $daysUntilDeparture <= $to;
        });

        // Filter for channel_id to ensure the channel matches the specified interval
        $filtered = $filtered->filter(function ($item) {
            $condition = collect($item['conditions'])->firstWhere('field', 'channel_id');
            $bearerToken = request()->bearerToken();
            $channelId = strval(Channel::where('access_token', 'like', "%$bearerToken")->first()?->token_id ?? 0);
            $conditionChannelId = $condition['value_from'] ?? null;
            $compare = $condition['compare'] ?? null;

            return ($conditionChannelId === $channelId && $compare === '=')
                || ($compare === '!=' && $conditionChannelId !== $channelId)
                || ! $condition;
        });

        // Filter for supplier_id to ensure the supplier matches the specified interval
        $filtered = $filtered->filter(function ($item) use ($supplierName) {
            $condition = collect($item['conditions'])->firstWhere('field', 'supplier_id');
            $supplierId = strval(Supplier::where('name', $supplierName)->first()?->id ?? 0);
            $conditionSupplierId = $condition['value_from'] ?? null;
            $compare = $condition['compare'] ?? null;

            return ($conditionSupplierId === $supplierId && $compare === '=')
                || ($compare === '!=' && $conditionSupplierId !== $supplierId)
                || ! $condition;
        });

        // Filter for total_guests to ensure the number of guests matches the specified interval
        $filtered = $filtered->filter(function ($item) use ($query) {
            $condition = collect($item['conditions'])->firstWhere('field', 'total_guests');
            $from = (int) ($condition['value_from'] ?? 0);
            $to = (int) ($condition['value_to'] ?? PHP_INT_MAX);

            $totalGuests = collect($query['occupancy'])->reduce(function ($accum, $item) {
                return $accum + (int) ($item['adults'] ?? 0) + (int) ($item['children'] ?? 0);
            }, 0);

            return $totalGuests > $from && $totalGuests < $to;
        });

        // Filter for number_of_rooms to ensure the number of rooms matches the specified interval
        $filtered = $filtered->filter(function ($item) use ($query) {
            $condition = collect($item['conditions'])->firstWhere('field', 'number_of_rooms');
            $from = (int) ($condition['value_from'] ?? 0);
            $to = (int) ($condition['value_to'] ?? PHP_INT_MAX);

            $numberOfRooms = count($query['occupancy']);

            return $numberOfRooms > $from && $numberOfRooms < $to;
        });

        // Filter for nights to ensure the number of nights matches the specified interval
        $filtered = $filtered->filter(function ($item) use ($checkin, $checkout) {
            $condition = collect($item['conditions'])->firstWhere('field', 'nights');
            $from = (int) ($condition['value_from'] ?? 0);
            $to = (int) ($condition['value_to'] ?? PHP_INT_MAX);

            $nights = $checkin->diffInDays($checkout);

            return $nights > $from && $nights < $to;
        });

        // Retain only intervals that are not nested within other intervals
        return $filtered->reject(function ($item) use ($filtered) {
            $condition = collect($item['conditions'])->firstWhere('field', 'date_of_stay');
            $from = Carbon::parse($condition['value_from'] ?? Carbon::now());
            $to = Carbon::parse($condition['value_to'] ?? Carbon::now()->addYears(1000));

            return $filtered->contains(function ($existing) use ($from, $to) {
                $existingCondition = collect($existing['conditions'])->firstWhere('field', 'date_of_stay');
                $existingFrom = Carbon::parse($existingCondition['value_from'] ?? Carbon::now());
                $existingTo = Carbon::parse($existingCondition['value_to'] ?? Carbon::now()->addYears(1000));

                // Check if the current interval ($from, $to) is fully nested within another interval
                return ($from >= $existingFrom && $to <= $existingTo && ($from != $existingFrom || $to != $existingTo))
                    && ($existingCondition['compare'] !== '=');
            });
        });
    }
}
