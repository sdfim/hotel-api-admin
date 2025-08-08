<?php

namespace Modules\API\Tools;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Repositories\ChannelRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PricingRulesTools
{
    public function rules(array $query, array $supplierRequestGiataIds, bool $is_exclude = false): array
    {
        // standalone driver must not apply prices rules DO NOT REMOVE
        if (isset($query['query_package']) && $query['query_package'] === 'standalone' && $query['supplier'] === 'Expedia') {
            return [];
        }

        $token = ChannelRepository::getTokenId(request()->bearerToken());

        \Log::info('TOKEN:', ['inspector' => [
            'token' => $token,
            'bearer' => request()->bearerToken(),
        ]]);

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

            ->where(function ($q) use ($channelId) {
                $q->whereHas('conditions', function ($q) use ($channelId) {
                    $q->where(function ($q) use ($channelId) {
                        $q->where('field', 'channel_id')
                            ->where('compare', '=')
                            ->where('value_from', $channelId);
                    })
                        ->orWhere(function ($q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', '!=')
                                ->whereNot('value_from', $channelId);
                        })
                        ->orWhere(function ($q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', 'in')
                                ->whereRaw('FIND_IN_SET(?, value)', [$channelId]);
                        })
                        ->orWhere(function ($q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', 'not_in')
                                ->whereRaw('NOT FIND_IN_SET(?, value)', [$channelId]);
                        });
                });

                // to include pricing rules that does not have filter by channel
                $q->orWhereDoesntHave('conditions', function ($query) {
                    $query->where('field', 'channel_id');
                });
            })

            ->whereDoesntHave('conditions', function (Builder $q) use (
                $supplierRequestGiataIds,
                $destination,
                $checkIn,
                $checkOut,
                $totalGuests,
                $daysUntilDeparture,
                $nights,
                $rating,
                $numberOfRooms
            ) {
                if (! empty($supplierRequestGiataIds)) {
                    $q->where(function (Builder $q) use ($supplierRequestGiataIds) {
                        $q->where('field', 'property')
                            ->where(function (Builder $q) use ($supplierRequestGiataIds) {
                                $q->where(function (Builder $q) use ($supplierRequestGiataIds) {
                                    $q->where('compare', '=')
                                        ->whereNotIn('value_from', $supplierRequestGiataIds);
                                })->orWhere(function (Builder $q) use ($supplierRequestGiataIds) {
                                    $q->where('compare', '!=')
                                        ->whereIn('value_from', $supplierRequestGiataIds);
                                })->orWhere(function (Builder $q) use ($supplierRequestGiataIds) {
                                    $q->where('compare', 'in')
                                        ->whereRaw('NOT FIND_IN_SET(value_from, ?)', [implode(',', $supplierRequestGiataIds)]);
                                })->orWhere(function (Builder $q) use ($supplierRequestGiataIds) {
                                    $q->where('compare', 'not_in')
                                        ->whereRaw('FIND_IN_SET(value_from, ?)', [implode(',', $supplierRequestGiataIds)]);
                                });
                            });
                    });
                }

                if ($destination) {
                    $q->orWhere(function (Builder $q) use ($destination) {
                        $q->where('field', 'destination')
                            ->where(function (Builder $q) use ($destination) {
                                $q->where(function (Builder $q) use ($destination) {
                                    $q->where('compare', '=')
                                        ->where('value_from', '!=', $destination);
                                })->orWhere(function (Builder $q) use ($destination) {
                                    $q->where('compare', '!=')
                                        ->where('value_from', $destination);
                                })->orWhere(function (Builder $q) use ($destination) {
                                    $q->where('compare', 'in')
                                        ->whereRaw('NOT FIND_IN_SET(?, value)', [$destination]);
                                })->orWhere(function (Builder $q) use ($destination) {
                                    $q->where('compare', 'not_in')
                                        ->whereRaw('FIND_IN_SET(?, value)', [$destination]);
                                });
                            });
                    });
                }

                if ($checkIn && $checkOut) {
                    $q->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                        $q->where('field', 'date_of_stay')
                            ->where(function (Builder $q) use ($checkIn, $checkOut) {
                                $q->where(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") != ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '!=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") = ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '<')
                                        ->whereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") >= ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '>')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") <= ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                                    $q->where('compare', 'between')
                                        ->where(function (Builder $q) use ($checkIn, $checkOut) {
                                            $q->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") > ?', [$checkOut])
                                                ->orWhereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") < ?', [$checkIn]);
                                        });
                                });
                            });
                    });

                    $q->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                        $q->where('field', 'booking_date')
                            ->where(function (Builder $q) use ($checkIn, $checkOut) {
                                $q->where(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") != ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '!=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") = ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '<')
                                        ->whereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") >= ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '>')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") <= ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                                    $q->where('compare', 'between')
                                        ->where(function (Builder $q) use ($checkIn, $checkOut) {
                                            $q->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") > ?', [$checkIn])
                                                ->orWhereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") < ?', [$checkOut]);
                                        });
                                });
                            });
                    });

                    $q->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                        $q->where('field', 'travel_date')
                            ->where(function (Builder $q) use ($checkIn, $checkOut) {
                                $q->where(function (Builder $q) use ($checkIn, $checkOut) {
                                    $q->where('compare', '=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") NOT BETWEEN ? AND ?', [$checkIn, $checkOut]);
                                })->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                                    $q->where('compare', '!=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") BETWEEN ? AND ?', [$checkIn, $checkOut]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '<')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") >= ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkOut) {
                                    $q->where('compare', '>')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") <= ?', [$checkOut]);
                                })->orWhere(function (Builder $q) use ($checkIn) {
                                    $q->where('compare', '<=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") < ?', [$checkIn]);
                                })->orWhere(function (Builder $q) use ($checkOut) {
                                    $q->where('compare', '>=')
                                        ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") > ?', [$checkOut]);
                                })->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                                    $q->where('compare', 'between')
                                        ->where(function (Builder $q) use ($checkIn, $checkOut) {
                                            $q->whereRaw('? > value_to OR ? < value_from', [$checkIn, $checkOut]);
                                        });
                                });
                            });
                    });

                }

                foreach ([
                    'total_guests' => $totalGuests,
                    'days_until_departure' => $daysUntilDeparture,
                    'nights' => $nights,
                    'rating' => $rating,
                    'number_of_rooms' => $numberOfRooms,
                ] as $field => $value) {
                    if ($value !== null) {
                        $q->orWhere(function (Builder $q) use ($field, $value) {
                            $q->where('field', $field)
                                ->where(function (Builder $q) use ($value) {
                                    $q->where(function (Builder $q) use ($value) {
                                        $q->where('compare', '=')
                                            ->where('value_from', '!=', $value);
                                    })->orWhere(function (Builder $q) use ($value) {
                                        $q->where('compare', '!=')
                                            ->where('value_from', '=', $value);
                                    })->orWhere(function (Builder $q) use ($value) {
                                        $q->where('compare', '<')
                                            ->where('value_to', '>=', $value);
                                    })->orWhere(function (Builder $q) use ($value) {
                                        $q->where('compare', '>')
                                            ->where('value_from', '<=', $value);
                                    })->orWhere(function (Builder $q) use ($value) {
                                        $q->where('compare', 'between')
                                            ->where('value_from', '>', $value)
                                            ->where('value_to', '<', $value);
                                    });
                                });
                        });
                    }
                }
            })

            // Checks if the rule is valid for the current date
            ->where(function (Builder $q) {
                $q->where('rule_start_date', '<=', now()->toDateString())
                    ->where('rule_expiration_date', '>=', now()->toDateString());
            })

            ->get()->toArray();
    }
}
