<?php

namespace Modules\API\Tools;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Models\Supplier;
use App\Repositories\ChannelRenository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PricingRulesTools
{
    /**
     * @param array $query
     * @return array
     */
    public function rules(array $query): array
    {
        $token = ChannelRenository::getTokenId(request()->bearerToken());

        $channelId = Channel::where('token_id', $token)->first()->id;

        $supplierId = Supplier::where('name', 'Expedia')->first()->id;

        $generalTools = new GeneralTools();

        $today = now();

        $carbonCheckIn = Carbon::parse($query['checkin']);

        $destination = $query['destination'] ?? null;

        $latitude = $query['latitude'] ?? null;

        $longitude = $query['longitude'] ?? null;

        $radius = $query['radius'] ?? null;

        if ($latitude && $longitude && $radius) {
            $geography = new Geography();

            $destination = $geography->findTheClosestCityInRadius($latitude, $longitude, $radius);
        }

        $totalGusts = $generalTools->calcTotalNumberOfGuestsInAllRooms($query['occupancy']);

        return PricingRule::where(function (Builder $q) use ($channelId) {
            $q->where('channel_id', null)
                ->orWhere('channel_id', $channelId);
        })
            ->where(function (Builder $q) use ($today, $carbonCheckIn) {
                $q->where('days_until_travel', null)
                    ->orWhere('days_until_travel', $today->diffInDays($carbonCheckIn));
            })
            ->where(function (Builder $q) use ($destination) {
                $q->where('destination', null);
                if ($destination) $q->orWhere('destination', $destination);
            })
            ->where(function (Builder $q) use ($query, $carbonCheckIn) {
                $q->where('nights', null)
                    ->orWhere('nights', Carbon::parse($query['checkout'])->diffInDays($carbonCheckIn));
            })
            ->where(function (Builder $q) use ($query) {
                $q->where('number_rooms', null)
                    ->orWhere('number_rooms', count($query['occupancy']));
            })
            ->where(function (Builder $q) use ($query) {
                $q->where('rating', null)
                    ->orWhere('rating', '>=', (float)$query['rating']);
            })
            ->whereDate('rule_start_date', '<=', $query['checkin'])
            ->whereDate('rule_expiration_date', '>=', $query['checkout'])
            ->where(function (Builder $q) use ($supplierId) {
                $q->where('supplier_id', null)
                    ->orWhere('supplier_id', $supplierId);
            })
            ->where(function (Builder $q) use ($totalGusts) {
                $q->where('total_guests', null)
                    ->orWhere('total_guests', $totalGusts);
            })
            ->where(function (Builder $q) use ($query) {
                $q->where('travel_date_from', null)
                    ->orWhereDate('travel_date_from', '<=', $query['checkin']);
            })
            ->where(function (Builder $q) use ($query) {
                $q->where('travel_date_to', null)
                    ->orWhereDate('travel_date_to', '>=', $query['checkin']);
            })
            ->get()
            ->toArray();
    }
}
