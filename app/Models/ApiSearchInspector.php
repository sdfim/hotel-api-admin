<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class ApiSearchInspector extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'api_search_inspector';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'search_id',
        'token_id',
        'suppliers',
        'search_type',
        'type',
        'request',
        'response_path',
        'client_response_path'
    ];

    /**
     * @return BelongsTo
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

	/**
     * Get the comments for the blog post.
     */
    public function apiBookingInspector(): HasMany
    {
        return $this->hasMany(ApiBookingInspector::class, 'search_id', 'search_id');
    }
	
	protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            Storage::delete($model->response_path);
			Storage::delete($model->client_response_path);
        });
    }

    /**
     * @param $filters
     * @return string
     */
    public function getLinckPriceCheck($filters): string
    {
        $search_id = $filters['search_id'];
        $hotel_id = $filters['hotel_id']; // giata_id
        $room_id = $filters['room_id']; // expedia
        $rate_id = $filters['rate'] ?? ''; // expedia
        $bed_groups = $filters['bed_groups'] ?? ''; // expedia

        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $json_response = json_decode(Storage::get($search_id->response_path));
        $rooms = $json_response->results->Expedia->$hotel_id->rooms;

        $linkPriceCheck = '';
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $rates = $room->rates;
                foreach ($rates as $rate) {
                    if ($rate->id == $rate_id) {
                        $linkPriceCheck = $rate->bed_groups->$bed_groups->links->price_check->href;
                    }
                }
                break;
            }
        }

        return $linkPriceCheck;
    }

    /**
     * @param string $search_id
     * @return string
     */
    public function geTypeBySearchId(string $search_id): string
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();
        return $search->search_type;
    }

    /**
     * @param $filters
     * @return array
     */
    public function getReservationsDataBySearchId($filters): array
    {
        $search_id = $filters['search_id'];
        $hotel_id = $filters['hotel_id']; // giata_id
        $room_id = $filters['room_id']; // expedia

        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $json_response = json_decode(Storage::get($search_id->client_response_path));

        $hotels = $json_response->results->Expedia;

        $price = [];
        foreach ($hotels as $hotel) {
            if ($hotel->giata_hotel_id != $hotel_id) continue;
            $hotel_id = $hotel->supplier_hotel_id;
            foreach ($hotel->room_groups as $room) {
                $price = [
                    'total_price' => $room->total_price,
                    'total_tax' => $room->total_tax,
                    'total_fees' => $room->total_fees,
                    'total_net' => $room->total_net,
                    'currency' => $room->currency,
                ];
            }
        }

        return ['query' => $json_response->query, 'price' => $price, 'supplier_hotel_id' => $hotel_id];
    }
}
