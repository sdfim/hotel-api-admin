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
        $validUltimateAmenities = [];
        foreach ($ultimateAmenities as $ultimateAmenity) {
            if ($this->filterUltimateAmenity($ultimateAmenity, $query, $roomResponse)) {
                $validUltimateAmenities = array_merge($validUltimateAmenities, $ultimateAmenity['amenities']);
            }
        }

        $requestConsortiaAffiliation = Arr::get($query, 'consortia_affiliation');

        $validUltimateAmenities = collect($validUltimateAmenities)
            ->filter(function ($affiliation) use ($requestConsortiaAffiliation) {
                return ! $requestConsortiaAffiliation || in_array($requestConsortiaAffiliation, Arr::get($affiliation, 'consortia', []));
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
                ];
            })
            ->toArray();
    }

    public function getTotalFeesAmount(array $feesUltimateAmenities): float
    {
        return array_sum(array_column($feesUltimateAmenities, 'amount'));
    }
}
