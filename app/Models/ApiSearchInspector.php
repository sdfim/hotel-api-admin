<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class ApiSearchInspector extends Model
{
    use HasFactory;

    protected $table = 'api_search_inspector';

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

    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

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

    public function geTypeBySearchId(string $search_id): string
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();
        return $search->search_type;
    }
}
