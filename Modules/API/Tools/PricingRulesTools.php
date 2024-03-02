<?php

namespace Modules\API\Tools;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Repositories\ChannelRenository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PricingRulesTools
{
    /**
     * @param array $query
     * @return array<PricingRule>
     */
    public function rules(array $query): array
    {
        $token = ChannelRenository::getTokenId(request()->bearerToken());
        $channelId = Channel::where('token_id', $token)->first()->id;

        $generalTools = new GeneralTools();

        $today = now();
        $checkIn = $query['checkin'];
        $checkOut = $query['checkout'];

        $destination = $query['destination'] ?? null;

        $latitude = $query['latitude'] ?? null;
        $longitude = $query['longitude'] ?? null;
        $radius = $query['radius'] ?? null;

        if ($latitude && $longitude && $radius) {
            $geography = new Geography();
            $destination = $geography->findTheClosestCityInRadius($latitude, $longitude, $radius);
        }

        $carbonCheckIn = Carbon::parse($checkIn);
        $daysUntilDeparture = $today->diffInDays($carbonCheckIn);
        $nights = Carbon::parse($query['checkout'])->diffInDays($carbonCheckIn);
        $rating = (float)$query['rating'] ?? 4.0;
        $numberOfRooms = count($query['occupancy']);
        $totalGuests = $generalTools->calcTotalNumberOfGuestsInAllRooms($query['occupancy']);


        return PricingRule::with('conditions')
            ->whereHas('conditions', function (Builder $q) use (
                $channelId,
                $destination,
                $checkIn,
                $checkOut,
                $totalGuests,
                $daysUntilDeparture,
                $nights,
                $rating,
                $numberOfRooms
            ) {
                $q->where(function (Builder $q) use ($channelId) {
                    $q->whereNot('field', 'channel_id')
                        ->orWhere(function (Builder $q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', '=')
                                ->where('value_from', $channelId);
                        });
                });

                $q->where(function (Builder $q) use ($destination) {
                    $q->whereNot('field', 'destination')
                        ->orWhere(function (Builder $q) use ($destination) {
                            $q->where('field', 'destination')
                                ->where('compare', '=')
                                ->where('value_from', $destination);
                        });
                });

                $q->where(function (Builder $q) use ($checkIn, $checkOut) {
                    $q->whereNot('field', 'travel_date')
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'travel_date')
                                ->where('compare', '=')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") = ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'travel_date')
                                ->where('compare', '<')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") < ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'travel_date')
                                ->where('compare', '>')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") > ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                            $q->where('field', 'travel_date')
                                ->where('compare', 'between')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") <= ?', [$checkIn])
                                ->whereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") >= ?', [$checkOut]);
                        });
                });

                $q->where(function (Builder $q) use ($checkIn, $checkOut) {
                    $q->whereNot('field', 'booking_date')
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'booking_date')
                                ->where('compare', '=')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") = ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'booking_date')
                                ->where('compare', '<')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") < ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'booking_date')
                                ->where('compare', '>')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") > ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                            $q->where('field', 'booking_date')
                                ->where('compare', 'between')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") <= ?', [$checkIn])
                                ->whereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") >= ?', [$checkOut]);
                        });
                });

                $q->where(function (Builder $q) use ($totalGuests) {
                    $q->whereNot('field', 'total_guests')
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', '=')
                                ->where('value_from', $totalGuests);
                        })
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', '<')
                                ->where('value_from', '<', $totalGuests);
                        })
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', '>')
                                ->where('value_from', '>', $totalGuests);
                        })
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $totalGuests);
                        });
                });

                $q->where(function (Builder $q) use ($daysUntilDeparture) {
                    $q->whereNot('field', 'days_until_departure')
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', '=')
                                ->where('value_from', $daysUntilDeparture);
                        })
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', '<')
                                ->where('value_from', '<', $daysUntilDeparture);
                        })
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', '>')
                                ->where('value_from', '>', $daysUntilDeparture);
                        })
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $daysUntilDeparture);
                        });
                });

                $q->where(function (Builder $q) use ($nights) {
                    $q->whereNot('field', 'nights')
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', '=')
                                ->where('value_from', $nights);
                        })
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', '<')
                                ->where('value_from', '<', $nights);
                        })
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', '>')
                                ->where('value_from', '>', $nights);
                        })
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $nights);
                        });
                });

                $q->where(function (Builder $q) use ($rating) {
                    $q->whereNot('field', 'rating')
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', '=')
                                ->where('value_from', $rating);
                        })
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', '<')
                                ->where('value_from', '<', $rating);
                        })
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', '>')
                                ->where('value_from', '>', $rating);
                        })
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $rating);
                        });
                });

                $q->where(function (Builder $q) use ($numberOfRooms) {
                    $q->whereNot('field', 'number_of_rooms')
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', '=')
                                ->where('value_from', $numberOfRooms);
                        })
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', '<')
                                ->where('value_from', '<', $numberOfRooms);
                        })
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', '>')
                                ->where('value_from', '>', $numberOfRooms);
                        })
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $numberOfRooms);
                        });
                });
            })->where(function (Builder $q) use ($query) {
                $q->where('rule_start_date', '<=', $query['checkin'])
                    ->where('rule_expiration_date', '>=', $query['checkout']);
            })->get()->toArray();
    }
}
