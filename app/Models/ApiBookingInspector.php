<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class ApiBookingInspector extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
		'booking_id',
        'token_id',
        'search_id',
		'supplier_id',
        'type',
		'sub_type',
        'request',
        'response_path',
		'client_response_path'
    ];

    public function token ()
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    public function supplier ()
    {
        return $this->belongsTo(Suppliers::class);
    }

	public function getLinckDeleteItem($filters)
	{
		$booking_id = $filters['booking_id'];
		$room_id = $filters['room_id'];

		$inspector = ApiBookingInspector::where('type', 'add_item')
			->where('sub_type', 'get_book')
			->where('booking_id', $booking_id)
			->first();

		$json_response = json_decode(Storage::get($inspector->response_path));
		$rooms = $json_response->rooms;

		$linkDeleteItem = '';
		foreach ($rooms as $room) {
			if ($room->id == $room_id) {
				$linkDeleteItem = $room->links->cancel->href;
				break;
			}
		}

		return $linkDeleteItem;
	}

	public function getItineraryId ($filters)
	{
		$booking_id = $filters['booking_id'];

		$inspector = ApiBookingInspector::where('type', 'add_item')
			->where('sub_type', 'get_book')
			->where('booking_id', $booking_id)
			->first();

		$json_response = json_decode(Storage::get($inspector->response_path));

		return $json_response->itinerary_id;
	}

}
