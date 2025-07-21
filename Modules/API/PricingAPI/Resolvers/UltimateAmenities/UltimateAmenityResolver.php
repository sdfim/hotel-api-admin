<?php

namespace Modules\API\PricingAPI\Resolvers\UltimateAmenities;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\API\PricingAPI\ResponseModels\RoomResponse;
use Modules\Enums\ProductApplyTypeEnum;

class UltimateAmenityResolver
{
    public function resolve(RoomResponse $roomResponse, array $ultimateAmenities, array $query): array
    {
        $numberOfNights = Carbon::parse($query['checkin'])->diffInDays(Carbon::parse($query['checkout']));
        $validUltimateAmenities = [];

        foreach ($ultimateAmenities as $ultimateAmenity) {
            if ($this->filterUltimateAmenity($ultimateAmenity, $query, $roomResponse)) {
                $validUltimateAmenities = array_merge($validUltimateAmenities, $ultimateAmenity['amenities']);
            }
        }

        $requestConsortiaAffiliation = Arr::get($query, 'consortia_affiliation');

        $validUltimateAmenities = collect($validUltimateAmenities)
            ->filter(function ($amenity) use ($requestConsortiaAffiliation, $numberOfNights) {
                $minNightStay = Arr::get($amenity, 'min_night_stay');
                $maxNightStay = Arr::get($amenity, 'max_night_stay');

                $validNightStay = ($minNightStay === null || $numberOfNights >= $minNightStay) &&
                    ($maxNightStay === null || $numberOfNights <= $maxNightStay);

                $validConsortia = ! $requestConsortiaAffiliation ||
                    in_array($requestConsortiaAffiliation, Arr::get($amenity, 'consortia', []));

                return $validNightStay && $validConsortia;
            })->toArray();

        return array_values($validUltimateAmenities);
    }

    private function filterUltimateAmenity(array $ultimateAmenity, array $query, RoomResponse $roomResponse): bool
    {
        $checkin = Carbon::parse($query['checkin']);
        $checkout = Carbon::parse($query['checkout']);
        $rateCode = $roomResponse->getRatePlanCode();
        $unifiedRoomCode = $roomResponse->getUnifiedRoomCode();
        $consortiaAffiliation = Arr::get($query, 'consortia_affiliation');

        $from = Arr::get($ultimateAmenity, 'start_date') ? Carbon::parse(Arr::get($ultimateAmenity, 'start_date')) : Carbon::now();
        $to = Arr::get($ultimateAmenity, 'end_date') ? Carbon::parse(Arr::get($ultimateAmenity, 'end_date')) : Carbon::now()->addYears(1000);

        return Carbon::now()->endOfDay()->isAfter($from)
            && $to->isAfter(Carbon::now()->startOfDay())
            && $checkin->between($from, $to)
            && $checkout->between($from, $to)
            && (Arr::get($ultimateAmenity, 'rate_code') === $rateCode || Arr::get($ultimateAmenity, 'rate_code') === null)
            && (Arr::get($ultimateAmenity, 'unified_room_code') === $unifiedRoomCode || Arr::get($ultimateAmenity, 'unified_room_code') === null)
            && collect(Arr::get($ultimateAmenity, 'amenities', []))
                ->contains(function ($amenity) use ($consortiaAffiliation) {
                    return ! $consortiaAffiliation || in_array($consortiaAffiliation, Arr::get($amenity, 'consortia', []));
                });
    }

    public function getFeesUltimateAmenities(array $roomUltimateAmenities, $numberOfPassengers, $checkin, $checkout): array
    {
        $numberOfNights = Carbon::parse($checkin)->diffInDays(Carbon::parse($checkout));

        return collect($roomUltimateAmenities)
            ->filter(function ($item) {
                return $item['is_paid'];
            })
            ->map(function ($item) use ($numberOfPassengers, $numberOfNights) {
                $amount = $item['price'];
                $amount *= match ($item['apply_type']) {
                    ProductApplyTypeEnum::PER_PERSON->value => $numberOfPassengers,
                    ProductApplyTypeEnum::PER_NIGHT->value => $numberOfNights,
                    ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => $numberOfPassengers * $numberOfNights,
                    default => 1, // Default case for PER_ROOM or other types
                };

                return [
                    'type' => 'amenity',
                    'amount' => $amount,
                    'title' => $item['name'],
                    'level' => $item['level']
                ];
            })
            ->toArray();
    }

    public function getTotalFeesAmount(array $feesUltimateAmenities): float
    {
        return array_sum(array_column($feesUltimateAmenities, 'amount'));
    }

    private function filterUltimateAmenityHotelLevel(array $ultimateAmenity, array $query): bool
    {
        $checkin = Carbon::parse($query['checkin']);
        $checkout = Carbon::parse($query['checkout']);
        $from = Arr::get($ultimateAmenity, 'start_date') ? Carbon::parse(Arr::get($ultimateAmenity, 'start_date')) : Carbon::now();
        $to = Arr::get($ultimateAmenity, 'end_date') ? Carbon::parse(Arr::get($ultimateAmenity, 'end_date')) : Carbon::now()->addYears(1000);

        return Carbon::now()->endOfDay()->isAfter($from)
            && $to->isAfter(Carbon::now()->startOfDay())
            && $checkin->between($from, $to)
            && $checkout->between($from, $to);
    }

    public function getHotelLevel(array $ultimateAmenities, array $query): array
    {
        $numberOfNights = Carbon::parse($query['checkin'])->diffInDays(Carbon::parse($query['checkout']));
        $requestConsortiaAffiliation = Arr::get($query, 'consortia_affiliation');

        $filtered = array_filter($ultimateAmenities, function ($affiliation) use ($query) {
            return (
                (!isset($affiliation['rate_code']) || $affiliation['rate_code'] === null)
                && (!isset($affiliation['unified_room_code']) || $affiliation['unified_room_code'] === null)
                && $this->filterUltimateAmenityHotelLevel($affiliation, $query)
            );
        });

        $filtered = collect($filtered)
            ->flatMap(function ($affiliation) {
                return collect($affiliation['amenities'] ?? [])->map(function ($amenity) {
                    $amenity['level'] = 'hotel';
                    return $amenity;
                });
            })
            ->filter(function ($amenity) use ($requestConsortiaAffiliation, $numberOfNights) {
                $minNightStay = Arr::get($amenity, 'min_night_stay');
                $maxNightStay = Arr::get($amenity, 'max_night_stay');

                $validNightStay = ($minNightStay === null || $numberOfNights >= $minNightStay) &&
                    ($maxNightStay === null || $numberOfNights <= $maxNightStay);

                $validConsortia = $this->isValidConsortia($requestConsortiaAffiliation, $amenity);

                return $validNightStay && $validConsortia;
            })
            ->toArray();

        return array_values($filtered);
    }

    public function getRateLevel(array $ultimateAmenities, array $query, RoomResponse $roomResponse): array
    {
        $numberOfNights = Carbon::parse($query['checkin'])->diffInDays(Carbon::parse($query['checkout']));
        $requestConsortiaAffiliation = Arr::get($query, 'consortia_affiliation');

        $filtered = array_filter($ultimateAmenities, function ($affiliation) use ($query, $roomResponse) {
            return (
                (isset($affiliation['rate_code']) && $affiliation['rate_code'] !== null)
                || (isset($affiliation['unified_room_code']) && $affiliation['unified_room_code'] !== null)
            ) && $this->filterUltimateAmenity($affiliation, $query, $roomResponse);
        });

        $filtered = collect($filtered)
            ->flatMap(function ($affiliation) {
                $level = 'rate';
                if (isset($affiliation['unified_room_code']) && $affiliation['unified_room_code'] !== null) {
                    $level = 'room';
                }
                return collect($affiliation['amenities'] ?? [])->map(function ($amenity) use ($level) {
                    $amenity['level'] = $level;
                    return $amenity;
                });
            })
            ->filter(function ($amenity) use ($requestConsortiaAffiliation, $numberOfNights) {
                $minNightStay = Arr::get($amenity, 'min_night_stay');
                $maxNightStay = Arr::get($amenity, 'max_night_stay');

                $validNightStay = ($minNightStay === null || $numberOfNights >= $minNightStay) &&
                    ($maxNightStay === null || $numberOfNights <= $maxNightStay);

                $validConsortia = $this->isValidConsortia($requestConsortiaAffiliation, $amenity);

                return $validNightStay && $validConsortia;
            })
            ->toArray();

        return array_values($filtered);
    }

    private function isValidConsortia(?string $consortia, array $amenity)
    {
        // Per SD-21268, the pricing search should only return applicable amenities,
        // if no consortia are passed, no amenities should be returned.
        return in_array($consortia, Arr::get($amenity, 'consortia', []));
    }
}
