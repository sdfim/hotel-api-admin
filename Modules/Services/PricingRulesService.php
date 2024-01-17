<?php

namespace Modules\Services;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Models\Supplier;
use App\Repositories\ChannelRenository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

class PricingRulesService
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

        $today = now();

        $fillable = [
            'total_guests',
        ];

        $carbonCheckIn = Carbon::parse($query['checkin']);
        $destination = $query['destination'] ?? null;
        $latitude = $query['latitude'] ?? null;
        $longitude = $query['longitude'] ?? null;

        return PricingRule::where('channel_id', function (Builder $q) use ($channelId) {
            $q->where('channel_id', null)
                ->orWhere('channel_id', $channelId);
        })
            ->where('days_until_travel', function (Builder $q) use ($today, $carbonCheckIn) {
                $q->where('days_until_travel', null)
                    ->orWhere('days_until_travel', $today->diffInDays($carbonCheckIn));
            })
            ->where('nights', function (Builder $q) use ($query, $carbonCheckIn) {
                $q->where('nights', null)
                    ->orWhere('nights', Carbon::parse($query['checkout'])->diffInDays($carbonCheckIn));
            })
            ->where('nights', function (Builder $q) use ($query, $carbonCheckIn) {
                $q->where('nights', null)
                    ->orWhere('nights', Carbon::parse($query['checkout'])->diffInDays($carbonCheckIn));
            })
            ->where('number_rooms', function (Builder $q) use ($query) {
                $q->where('number_rooms', null)
                    ->orWhere('number_rooms', count($query['occupancy']));
            })
//            ->where('property', function (Builder $q) use ($destination) {
//                $q->where('property', null);
//                if ($destination) $q->orWhere('property', $destination);
//            })
            ->where('rating', function (Builder $q) use ($query) {
                $q->where('rating', null)
                    ->orWhere('rating', '>=', (float)$query['rating']);
            })
            ->where('supplier_id', function (Builder $q) use ($supplierId) {
                $q->where('supplier_id', null)
                    ->orWhere('supplier_id', $supplierId);
            })
            ->whereDate('rule_start_date', '<=', $query['checkin'])
            ->whereDate('rule_expiration_date', '>=', $query['checkout'])
            ->where('travel_date_from', function (Builder $q) use ($query) {
                $q->where('travel_date_from', null)
                    ->orWhereDate('travel_date_from', '<=', $query['checkin']);
            })
            ->where('travel_date_to', function (Builder $q) use ($query) {
                $q->where('travel_date_to', null)
                    ->orWhereDate('travel_date_to', '>=', $query['checkin']);
            })
            ->get()
            ->toArray();
    }
}
