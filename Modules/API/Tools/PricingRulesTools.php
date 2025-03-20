<?php

namespace Modules\API\Tools;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Repositories\ChannelRenository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PricingRulesTools
{
    public function rules(array $query, bool $is_exclude = false): array
    {
        // standalone driver must not apply prices rules DO NOT REMOVE
        if(isset($query["query_package"]) && $query["query_package"] === "standalone" &&  $query["supplier"] === 'Expedia')
        {
            return [];
        }

        $type = Arr::get($query, 'type', 'hotel');

        $token = ChannelRenository::getTokenId(request()->bearerToken());
        $channelId = Channel::where('token_id', $token)->first()->id;

        /** @var GeneralTools $generalTools */
        $generalTools = app(GeneralTools::class);

        $today = now();
        $checkIn = Arr::get($query, 'checkin', null);
        $checkOut = Arr::get($query, 'checkout', null);

        $destination = Arr::get($query, 'destination', null);

        $latitude = Arr::get($query, 'latitude', null);
        $longitude = Arr::get($query, 'longitude', null);
        $radius = Arr::get($query, 'radius', null);

        if ($latitude && $longitude && $radius) {
            /** @var Geography $geography */
            $geography = app(Geography::class);
            $destination = $geography->findTheClosestCityInRadius($latitude, $longitude, $radius);
        }

        $carbonCheckIn = null;
        $daysUntilDeparture = null;
        $nights = null;
        $rating = null;
        $numberOfRooms = null;
        $totalGuests = null;
        if ($checkIn && $checkOut) {
            $carbonCheckIn = Carbon::parse($checkIn);
            $daysUntilDeparture = floor($today->diffInDays($carbonCheckIn, true));
            $nights = floor(Carbon::parse($query['checkout'])->diffInDays($carbonCheckIn, true));
            $rating = Arr::get($query, 'rating', 4.0);
            $numberOfRooms = count($query['occupancy']);
            $totalGuests = $generalTools->calcTotalNumberOfGuestsInAllRooms($query['occupancy']);
        }

        return PricingRule::with(['conditions'])
//            ->where('product_type', $type)
            ->where('is_exclude_action', $is_exclude)

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
                        })
                        ->orWhere(function (Builder $q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', '!=')
                                ->whereNot('value_from', $channelId);
                        })
                        ->orWhere(function (Builder $q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', 'in')
                                ->whereRaw('FIND_IN_SET(?, value)', [$channelId]);
                        })
                        ->orWhere(function (Builder $q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', 'not_in')
                                ->whereRaw('NOT FIND_IN_SET(?, value)', [$channelId]);
                        });
                });

                if ($destination) {
                    $q->where(function (Builder $q) use ($destination) {
                        $q->whereNot('field', 'destination')
                            ->orWhere(function (Builder $q) use ($destination) {
                                $q->where('field', 'destination')
                                    ->where('compare', '=')
                                    ->where('value_from', $destination);
                            })
                            ->orWhere(function (Builder $q) use ($destination) {
                                $q->where('field', 'destination')
                                    ->where('compare', '!=')
                                    ->whereNot('value_from', $destination);
                            })
                            ->orWhere(function (Builder $q) use ($destination) {
                                $q->where('field', 'destination')
                                    ->where('compare', 'in')
                                    ->whereRaw('FIND_IN_SET(?, value)', [$destination]);
                            })
                            ->orWhere(function (Builder $q) use ($destination) {
                                $q->where('field', 'destination')
                                    ->where('compare', 'not_in')
                                    ->whereRaw('NOT FIND_IN_SET(?, value)', [$destination]);
                            });
                    });
                }

                if ($checkIn && $checkOut) {
                    $q->where(function (Builder $q) use ($checkIn, $checkOut) {
                        $q->whereNot('field', 'travel_date')
                            ->orWhere(function (Builder $q) use ($checkIn) {
                                $q->where('field', 'travel_date')
                                    ->where('compare', '=')
                                    ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") = ?', [$checkIn]);
                            })
                            ->orWhere(function (Builder $q) use ($checkIn) {
                                $q->where('field', 'travel_date')
                                    ->where('compare', '!=')
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
                                    ->where('compare', '!=')
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
                }

                if ($totalGuests) {
                    $q->where(function (Builder $q) use ($totalGuests) {
                        $q->whereNot('field', 'total_guests')
                            ->orWhere(function (Builder $q) use ($totalGuests) {
                                $q->where('field', 'total_guests')
                                    ->where('compare', '=')
                                    ->where('value_from', $totalGuests);
                            })
                            ->orWhere(function (Builder $q) use ($totalGuests) {
                                $q->where('field', 'total_guests')
                                    ->where('compare', '!=')
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
                }

                if ($daysUntilDeparture) {
                    $q->where(function (Builder $q) use ($daysUntilDeparture) {
                        $q->whereNot('field', 'days_until_departure')
                            ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                                $q->where('field', 'days_until_departure')
                                    ->where('compare', '=')
                                    ->where('value_from', $daysUntilDeparture);
                            })
                            ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                                $q->where('field', 'days_until_departure')
                                    ->where('compare', '!=')
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
                }

                if ($nights) {
                    $q->where(function (Builder $q) use ($nights) {
                        $q->whereNot('field', 'nights')
                            ->orWhere(function (Builder $q) use ($nights) {
                                $q->where('field', 'nights')
                                    ->where('compare', '=')
                                    ->where('value_from', $nights);
                            })
                            ->orWhere(function (Builder $q) use ($nights) {
                                $q->where('field', 'nights')
                                    ->where('compare', '!=')
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
                }

                if ($rating) {
                    $q->where(function (Builder $q) use ($rating) {
                        $q->whereNot('field', 'rating')
                            ->orWhere(function (Builder $q) use ($rating) {
                                $q->where('field', 'rating')
                                    ->where('compare', '=')
                                    ->where('value_from', $rating);
                            })
                            ->orWhere(function (Builder $q) use ($rating) {
                                $q->where('field', 'rating')
                                    ->where('compare', '!=')
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
                }

                if ($numberOfRooms) {
                    $q->where(function (Builder $q) use ($numberOfRooms) {
                        $q->whereNot('field', 'number_of_rooms')
                            ->orWhere(function (Builder $q) use ($numberOfRooms) {
                                $q->where('field', 'number_of_rooms')
                                    ->where('compare', '=')
                                    ->where('value_from', $numberOfRooms);
                            })
                            ->orWhere(function (Builder $q) use ($numberOfRooms) {
                                $q->where('field', 'number_of_rooms')
                                    ->where('compare', '!=')
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
                }
            })

            ->where(function (Builder $q) use ($query) {
                $q->where('rule_start_date', '<=', $query['checkin'])
                    ->where('rule_expiration_date', '>=', $query['checkout']);
            })

            ->get()->toArray();
    }
}
